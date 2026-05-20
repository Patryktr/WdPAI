const setActiveChoice = (picker, selector, activeValue, dataKey) => {
  picker?.querySelectorAll(selector).forEach((button) => {
    button.classList.toggle('active', button.dataset[dataKey] === activeValue);
  });
};

document.addEventListener('click', (event) => {
  const iconChoice = event.target.closest('.icon-choice');
  const colorChoice = event.target.closest('.color-choice');

  if (iconChoice) {
    const picker = iconChoice.closest('.icon-picker');
    const form = iconChoice.closest('form');
    const iconInput = form?.querySelector('input[name="icon"]');
    const customIconInput = form?.querySelector('[data-category-icon-custom]');
    const iconValue = iconChoice.dataset.icon || '';

    if (!picker || !iconInput) {
      return;
    }

    iconInput.value = iconValue;

    if (customIconInput) {
      customIconInput.value = iconValue;
    }

    setActiveChoice(picker, '.icon-choice', iconValue, 'icon');
  }

  if (colorChoice) {
    const picker = colorChoice.closest('.color-picker');
    const form = colorChoice.closest('form');
    const colorInput = form?.querySelector('input[name="color"]');
    const customColorInput = form?.querySelector('[data-category-color-custom]');
    const colorValue = colorChoice.dataset.color || '';

    if (!picker || !colorInput) {
      return;
    }

    colorInput.value = colorValue;

    if (customColorInput) {
      customColorInput.value = colorValue;
    }

    setActiveChoice(picker, '.color-choice', colorValue, 'color');
  }
});

document.addEventListener('input', (event) => {
  const customIconInput = event.target.closest('[data-category-icon-custom]');
  const customColorInput = event.target.closest('[data-category-color-custom]');

  if (customIconInput) {
    const form = customIconInput.closest('form');
    const iconInput = form?.querySelector('input[name="icon"]');
    const picker = form?.querySelector('.icon-picker');
    const iconValue = customIconInput.value.trim();

    if (iconInput) {
      iconInput.value = iconValue;
    }

    setActiveChoice(picker, '.icon-choice', iconValue, 'icon');
  }

  if (customColorInput) {
    const form = customColorInput.closest('form');
    const colorInput = form?.querySelector('input[name="color"]');
    const picker = form?.querySelector('.color-picker');
    const colorValue = customColorInput.value.trim();

    if (colorInput) {
      colorInput.value = colorValue;
    }

    setActiveChoice(picker, '.color-choice', colorValue, 'color');
  }
});

const readJsonScript = (id) => {
  const node = document.getElementById(id);

  if (!node) {
    return [];
  }

  try {
    const parsed = JSON.parse(node.textContent || '[]');
    return Array.isArray(parsed) ? parsed : [];
  } catch (error) {
    return [];
  }
};

const formatMoney = (value) => Number(value || 0).toLocaleString(undefined, {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

const renderMonthlyChart = () => {
  const chart = document.querySelector('[data-monthly-chart]');
  const data = readJsonScript('statistics-monthly-data');

  if (!chart || data.length === 0) {
    return;
  }

  const maxTotal = Math.max(...data.map((item) => Number(item.total) || 0), 1);
  chart.replaceChildren();
  chart.classList.add('statistics-column-chart');

  data.forEach((item) => {
    const value = Number(item.total) || 0;
    const height = Math.max(8, Math.round((value / maxTotal) * 100));
    const bar = document.createElement('div');
    const fill = document.createElement('span');
    const label = document.createElement('small');
    const amount = document.createElement('strong');

    bar.className = 'statistics-column';
    fill.style.height = `${height}%`;
    label.textContent = item.label || '';
    amount.textContent = formatMoney(value);

    bar.append(fill, amount, label);
    chart.append(bar);
  });
};

const renderCategoryShareChart = () => {
  const chart = document.querySelector('[data-category-chart]');
  const data = readJsonScript('statistics-category-data');

  if (!chart || data.length === 0) {
    return;
  }

  const total = data.reduce((sum, item) => sum + (Number(item.total) || 0), 0);

  if (total <= 0) {
    return;
  }

  const colors = ['#25ff16', '#69a7ff', '#b18cff', '#ff8fd5', '#67e8f9', '#facc15', '#fb923c', '#94a3b8'];
  const shareBar = document.createElement('div');
  const legend = document.createElement('div');

  chart.replaceChildren();
  chart.classList.add('statistics-share-chart');
  shareBar.className = 'statistics-share-bar';
  legend.className = 'statistics-share-legend';

  data.forEach((item, index) => {
    const value = Number(item.total) || 0;
    const percent = total > 0 ? (value / total) * 100 : 0;
    const color = colors[index % colors.length];
    const segment = document.createElement('span');
    const legendItem = document.createElement('div');
    const marker = document.createElement('i');
    const label = document.createElement('span');
    const amount = document.createElement('strong');

    segment.style.width = `${percent}%`;
    segment.style.background = color;
    segment.title = `${item.label || ''}: ${formatMoney(value)}`;

    marker.style.background = color;
    label.textContent = `${item.label || ''} (${Math.round(percent)}%)`;
    amount.textContent = formatMoney(value);

    legendItem.append(marker, label, amount);
    shareBar.append(segment);
    legend.append(legendItem);
  });

  chart.append(shareBar, legend);
};

document.addEventListener('DOMContentLoaded', () => {
  renderMonthlyChart();
  renderCategoryShareChart();
});
