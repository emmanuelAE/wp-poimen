createElement = function createElement(tagName, options = {}) {
    const element = document.createElement(tagName);
    for (const [key, value] of Object.entries(options)) {
        element.setAttribute(key, value);
    }
    return element;
}

createOptions = function createOptions() {
    for (let value of datas.dropdownOptionName) {
        const option = createElement('option', { value: value });
        option.textContent = value; 
        select.appendChild(option);
    }
}