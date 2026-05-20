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
