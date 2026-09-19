document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.querySelector('.overlay');
    const actionButtons = document.querySelectorAll('.action-btn');
    const buttonsClose = document.querySelectorAll('.button--close');

    actionButtons.forEach(function (el) {
        el.addEventListener('click', function (evt) {
            const modalType = evt.currentTarget.dataset.action;
            const modal = document.querySelector('.pop-up--' + modalType);

            if (!modal || !overlay) {
                return;
            }

            modal.classList.remove('pop-up--close');
            modal.classList.add('pop-up--open');
            overlay.classList.add('db');
        });
    });

    buttonsClose.forEach(function (el) {
        el.addEventListener('click', function () {
            const modalOpen = document.querySelector('.pop-up--open');

            if (!modalOpen || !overlay) {
                return;
            }

            modalOpen.classList.remove('pop-up--open');
            modalOpen.classList.add('pop-up--close');
            overlay.classList.remove('db');
        });
    });
});
