const createElement = function createElement(tagName, options = {}) {
    const element = document.createElement(tagName);
    for (const [key, value] of Object.entries(options)) {
        element.setAttribute(key, value);
    }
    return element;
}

const createOptions = function createOptions(element, optionList) {
    for (let ob of optionList) {
        const option = createElement('option', { value: ob });
        option.textContent = ob; 
        element.append(option);
    }
}
const createForm = function createForm(modal, lastSubmittedFormInfoObject) {
    modal.innerHTML = '';
    const form = createElement('form', { class: 'form-container' });
    console.log(Object.keys(lastSubmittedFormInfoObject).length);
    if (Object.keys(lastSubmittedFormInfoObject).length === 0) {
        const formGroup = createElement('div', { class: 'form-group' });
        const label = createElement('label');
        label.textContent = "Désolé, cette fonctionnalité n'était pas disponible lors du dernier rapport du L.A\nMais pas d'inquiétude vous pourrez retrouver le prochain rapport ici."
        formGroup.appendChild(label);
        form.appendChild(formGroup);
        modal.appendChild(form);
        console.log('No data' );
        console.log(formGroup);
        return;
    }
    for (let [key, value] of Object.entries(lastSubmittedFormInfoObject)) {
        const formGroup = createElement('div', { class: 'form-group' });
        const label = createElement('label', { for: key });
        label.textContent = key;
        const input = value.length > 10 ? createElement('textarea', { id: key, readonly: true }) : createElement('input', { type: 'text', value: value, id: key, readonly: true });
        input.value = value;
        formGroup.appendChild(label);
        formGroup.appendChild(input);
        form.appendChild(formGroup);
    }
    modal.appendChild(form);
}


const __getFormData = function __getFormData(soulName, selectedUserObject) {
    let lastSubmittedFormInfoObject = {};
    if (selectedUserObject.hasOwnProperty('userAssociatedClients')) {
        var associatedClients = selectedUserObject.userAssociatedClients;
        Object.keys(associatedClients).forEach(function(key) {
            if (associatedClients[key].hasOwnProperty('last_submitted_form_info') && associatedClients[key].last_submitted_form_info["Nom de l'âme"] === soulName) {
                lastSubmittedFormInfoObject = associatedClients[key].last_submitted_form_info;
            }
        });
    }
    return lastSubmittedFormInfoObject;
}

const createModalWindow = function createModalWindow(soulName, selectedUserObject) {
	console.log(selectedUserObject) ;
    const lastSubmittedFormInfoObject = __getFormData(soulName, selectedUserObject);
    const modalContainer = createElement('div', { class: 'overlay modal-container' });
    const modalOverlay = createElement('div', { class: 'modal-overlay' });
    const modal = createElement('div', { class: 'modal' });

    createForm(modal, lastSubmittedFormInfoObject);
    modalContainer.append(modalOverlay, modal);
    document.body.appendChild(modalContainer);

    setTimeout(() => modal.classList.add('active'), 100);

    // Empêcher le défilement de la page principale lorsque la fenêtre modale est ouverte
    modalOverlay.addEventListener('click', closeModal)
    document.body.classList.add('body-no-scroll');
}

const closeModal = function closeModal() {
    const modalContainer = document.querySelector('.modal-container');
    const modal = document.querySelector('.modal');

    modal.classList.remove('active');

    setTimeout(() => {
        modalContainer.remove();
        // Réactiver le défilement de la page principale lorsque la fenêtre modale est fermée
        document.body.classList.remove('body-no-scroll');
    }, 200);
}

const getUsers = function getUsers() {
    let users = [];
    for (let user of datas.dropdownOptionName) {
        users.push({
            userId: user.ID,
            userLogin: user.user_login,
            userAssociatedClients: user.user_meta.associated_clients,
            userLastFormInfos: user.user_meta.last_submitted_form_info
        });
    }
	console.log(users);
    return users;
}

const getUserNames = function getUserNames() {
    let userNames = [];
    for (let user of users) {
        userNames.push(user.userLogin);
    }
    return userNames;
}

const createReportButton = function createReportButton() {
    const button = createElement('button', { class: 'report-button modal-trigger' });
    const icon = createElement('i', { class: 'fa-regular fa-clipboard' });
    button.appendChild(icon);
    return button;
}


const fillListe = function fillListe() {
    let selectedUserSoul = users.find(user => user.userLogin === selectedUser);
    let associatedClients = selectedUserSoul.userAssociatedClients;
    liste.innerHTML = '';
    for (let soul of Object.values(associatedClients)) {
        let soulElement = createElement('li');
        soulElement.textContent = soul.name;

        const reportButton = createReportButton();
        soulElement.appendChild(reportButton);

        liste.append(soulElement);
    }
}

const users = getUsers();
console.log(datas);
var selectedUser = '';
select = document.querySelector('#leader-accompagnateur');
liste = document.querySelector(`.custom-list`);

if (select && liste) {
    select.innerHTML = '<option value="selectionner">selectionner un Leader--</option>';
    liste.innerHTML = '<li>Selectionner une leader</li>';

    document.addEventListener('DOMContentLoaded', () => {
        createOptions(select, getUserNames());
    });

    select.addEventListener('change', () => {
        selectedUser = select.value;
        fillListe();
        const buttonTriggers = document.querySelectorAll('.report-button');
        buttonTriggers.forEach(trigger => {
            trigger.addEventListener('click', (event) => {
                soulName = event.target.parentNode.parentNode.textContent;
                selectedUserObject = users.find(user => user.userLogin === selectedUser);
                createModalWindow(soulName, selectedUserObject);
            });
        });
    });
}
