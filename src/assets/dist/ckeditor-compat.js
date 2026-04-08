(function () {
  const basePluginMap = {
    alignment: ['Alignment'],
    blockQuote: ['BlockQuote'],
    bold: ['Bold'],
    code: ['Code'],
    codeBlock: ['CodeBlock'],
    bulletedList: ['List', 'ListProperties'],
    numberedList: ['List', 'ListProperties'],
    findAndReplace: ['FindAndReplace'],
    fontSize: ['Font'],
    fontFamily: ['Font'],
    fontColor: ['Font'],
    fontBackgroundColor: ['Font'],
    forcePlainTextPaste: ['ForcePlainTextPaste'],
    heading: ['Heading'],
    horizontalLine: ['HorizontalLine'],
    htmlEmbed: ['HtmlEmbed'],
    outdent: ['Indent', 'IndentBlock'],
    indent: ['Indent', 'IndentBlock'],
    italic: ['Italic'],
    link: ['Link', 'AutoLink'],
    mediaEmbed: ['MediaEmbed', 'MediaEmbedToolbar'],
    pageBreak: ['PageBreak'],
    removeFormat: ['RemoveFormat'],
    sourceEditing: ['SourceEditing'],
    strikethrough: ['Strikethrough'],
    style: ['Style'],
    subscript: ['Subscript'],
    superscript: ['Superscript'],
    insertTable: ['Table', 'TableCaption', 'TableCellProperties', 'TableProperties', 'TableToolbar', 'TableUI'],
    textPartLanguage: ['TextPartLanguage'],
    todoList: ['TodoList'],
    underline: ['Underline'],
  };

  const essentials = ['Essentials', 'Paragraph', 'Clipboard', 'SelectAll', 'GeneralHtmlSupport'];
  const alwaysSupportedItems = new Set(['|', '-', 'undo', 'redo']);
  const textSlashIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L355.7 253.5 400.2 96 503 96 497 120.2c-4.3 17.1 6.1 34.5 23.3 38.8s34.5-6.1 38.8-23.3l11-44.1C577.6 61.3 554.7 32 523.5 32L376.1 32l-.3 0L204.5 32c-22 0-41.2 15-46.6 36.4l-6.3 25.2L38.8 5.1zm168 131.7c.1-.3 .2-.7 .3-1L217 96l116.7 0L301.3 210.8l-94.5-74.1zM243.3 416L192 416c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-42.2 0 17.6-62.1L272.9 311 243.3 416z"></path></svg>';

  const waitForCkeditor = () => new Promise((resolve, reject) => {
    let attempts = 0;
    const maxAttempts = 100;

    const check = () => {
      if (window.CKEDITOR && window.CKEDITOR.ClassicEditor) {
        resolve(window.CKEDITOR);
        return;
      }

      attempts++;

      if (attempts >= maxAttempts) {
        reject(new Error('CKEditor UMD runtime did not become available.'));
        return;
      }

      window.setTimeout(check, 50);
    };

    check();
  });

  const ensureForcePlainTextPastePlugin = (CKEDITOR) => {
    window.CKEditor5 = window.CKEditor5 || {};
    window.CKEditor5.inspireCkeditorPlaintext =
      window.CKEditor5.inspireCkeditorPlaintext || {};

    if (window.CKEditor5.inspireCkeditorPlaintext.ForcePlainTextPaste) {
      return;
    }

    class ForcePlainTextPasteCommand extends CKEDITOR.Command {
      constructor(editor) {
        super(editor);
        this.set('value', false);
      }

      refresh() {
        this.isEnabled = true;
      }

      execute() {
        this.value = !this.value;
      }
    }

    class ForcePlainTextPaste extends CKEDITOR.Plugin {
      static get pluginName() {
        return 'ForcePlainTextPaste';
      }

      init() {
        const editor = this.editor;
        const commandName = 'forcePlainTextPaste';
        const clipboardPipeline = editor.plugins.get('ClipboardPipeline');

        editor.commands.add(commandName, new ForcePlainTextPasteCommand(editor));

        editor.ui.componentFactory.add(commandName, (locale) => {
          const button = new CKEDITOR.ButtonView(locale);
          const command = editor.commands.get(commandName);

          button.set({
            label: editor.t('Paste plain text'),
            tooltip: true,
            withText: false,
            isToggleable: true,
            icon: textSlashIcon,
          });

          button.bind('isEnabled').to(command, 'isEnabled');
          button.bind('isOn').to(command, 'value');

          this.listenTo(button, 'execute', () => {
            editor.execute(commandName);
          });

          return button;
        });

        editor.editing.view.document.on(
          'clipboardInput',
          (event, data) => {
            const command = editor.commands.get(commandName);

            if (!command?.value) {
              return;
            }

            const dataTransfer = data.dataTransfer;
            if (!dataTransfer || typeof dataTransfer.getData !== 'function') {
              return;
            }

            const text = dataTransfer.getData('text/plain');
            if (typeof text !== 'string' || text.length === 0) {
              return;
            }

            event.stop();
            data.content = editor.data.htmlProcessor.toView(
              text
                .replace(/\u00a0/g, ' ')
                .replace(/\t/g, ' ')
                .replace(/\r\n/g, '\n')
                .replace(/\r/g, '\n')
                .split('\n')
                .map((line) => line.trim())
                .filter((line) => line !== '')
                .map((line) => line
                  .replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;'))
                .map((line) => `<p>${line}</p>`)
                .join('')
            );
            clipboardPipeline.fire('inputTransformation', data);
          },
          {priority: 'highest'}
        );
      }
    }

    window.CKEditor5.inspireCkeditorPlaintext.ForcePlainTextPaste =
      ForcePlainTextPaste;
  };

  const flattenToolbarItems = (items) => {
    if (!Array.isArray(items)) {
      return [];
    }

    return items.flatMap((item) => (Array.isArray(item) ? item : [item]));
  };

  const resolvePluginConstructor = (CKEDITOR, name) => {
    if (CKEDITOR?.[name]) {
      return CKEDITOR[name];
    }

    const namespaces = Object.values(window.CKEditor5 || {});

    for (const namespace of namespaces) {
      if (namespace && typeof namespace === 'object' && namespace[name]) {
        return namespace[name];
      }
    }

    return null;
  };

  const getPluginConstructors = (CKEDITOR, config) => {
    const names = new Set(essentials);
    const toolbarItems = flattenToolbarItems(config?.toolbar?.items || []);

    toolbarItems.forEach((item) => {
      (basePluginMap[item] || []).forEach((name) => names.add(name));
    });

    if (config?.heading?.options?.length) {
      names.add('Heading');
    }

    return Array.from(names)
      .map((name) => resolvePluginConstructor(CKEDITOR, name))
      .filter(Boolean);
  };

  const getToolbarItems = (config) => {
    const toolbarItems = flattenToolbarItems(config?.toolbar?.items || []);

    return toolbarItems.filter((item) => {
      if (alwaysSupportedItems.has(item)) {
        return true;
      }

      return !!basePluginMap[item];
    });
  };

  const syncSourceElement = (editor) => {
    if (!editor || !editor.sourceElement) {
      return;
    }

    editor.sourceElement.value = editor.getData();
  };

  const getFormidableConfigType = (element) => {
    if (!element || !element.classList) {
      return null;
    }

    const classNames = Array.from(element.classList);

    if (classNames.some((className) => className.startsWith('field-label-value'))) {
      return 'label';
    }

    if (classNames.some((className) => className.startsWith('field-tooltip-value'))) {
      return 'tooltip';
    }

    return null;
  };

  const getPreferredConfig = (element, config) => {
    const configType = getFormidableConfigType(element);
    const configuredEditors = window.formidableCkeditorConfigs || {};

    if (configType && configuredEditors[configType]) {
      return configuredEditors[configType];
    }

    return config || {};
  };

  window.CKEditor5 = window.CKEditor5 || {};
  window.CKEditor5.craftcms = window.CKEditor5.craftcms || {};

  window.CKEditor5.craftcms.create = function (element, config = {}) {
    if (typeof element === 'string') {
      element = document.querySelector('#' + element);
    }

    const preferredConfig = getPreferredConfig(element, config);

    return waitForCkeditor().then((CKEDITOR) => {
      ensureForcePlainTextPastePlugin(CKEDITOR);
      const toolbarItems = getToolbarItems(preferredConfig);
      const editorConfig = {
        ...preferredConfig,
        licenseKey: 'GPL',
        plugins: getPluginConstructors(CKEDITOR, preferredConfig),
        toolbar: {
          items: toolbarItems,
        },
      };

      return CKEDITOR.ClassicEditor.create(element, editorConfig).then((editor) => {
        syncSourceElement(editor);
        editor.model.document.on('change:data', () => {
          syncSourceElement(editor);
        });
        return editor;
      });
    });
  };
})();
