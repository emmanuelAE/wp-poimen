const createElement = function createElement(tagName, options = {}) {
    const element = document.createElement(tagName);
    for (const [key, value] of Object.entries(options)) {
        element.setAttribute(key, value);
    }
    return element;
}

const createOptions = function createOptions(element,optionList) {
    for (let ob of optionList) {
        console.log(ob);
        const option = createElement('option', { value: ob });
        option.textContent = ob; 
        element.append(option);
    }
}

const getUsers = function getUsers() {
    let users = [];
    for (let user of datas.dropdownOptionName) {
        users.push({
            userId : user.data.ID,
            userLogin: user.data.user_login,
            userAssociatedClients: user.data.associated_clients
        });
    }
    return users;
}

const users = getUsers();
let selectedUser = ''

select = document.querySelector('#leader-accompagnateur');
liste = document.querySelector(`.custom-list`);

const getUserNames = function getUserNames() {
    let userNames = [];
    for (let user of users) {
        userNames.push(user.userLogin);
    }
    return userNames;

}

fillListe = function fillListe() {
    let selectedUserSoul = users.find(user => user.userLogin === selectedUser);
    let associatedClients = selectedUserSoul.userAssociatedClients;
    liste.innerHTML = '';
    for(let soul of Object.values(associatedClients)) {
        let soulElement = createElement('li');
        soulElement.textContent = soul.name;
        liste.append(soulElement);
    }
}

if (select && liste) {
    select.innerHTML = '<option value = "selectionner">selectionner un Leader--</option>';
    liste.innerHTML = '<li>Selectionner une leader</li>' ; 
    document.addEventListener('DOMContentLoaded', () => {
        createOptions(select, getUserNames());
    });
    select.addEventListener('change', () => {
        selectedUser = select.value;
        fillListe();
    })

}
