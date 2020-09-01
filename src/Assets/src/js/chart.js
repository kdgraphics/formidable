import Chart from 'chart.js/dist/Chart';

class SubmissionChart
{
	constructor(elem, type)
	{
		this.elem = document.getElementById(elem).getContext('2d');
		this.type = type;
		this.chart = null;
	}

	load(labels, data)
	{
		if (!this.chart) {
			this.chart = new Chart(this.elem, {
				type: 'line',
				data: {
					labels: labels,
					datasets:data
				}
			});
		} else {
			this.update(labels, data);
		}
	}

	update(labels, data)
	{
		this.chart.data.labels = labels;
		this.chart.data.datasets = data;
		this.chart.update();
	}
}

export default SubmissionChart;