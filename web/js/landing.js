var openModalLinks = document.getElementsByClassName("open-modal");
var closeModalLinks = document.getElementsByClassName("form-modal-close");
var overlay = document.getElementsByClassName("overlay")[0];
var loginForm = document.getElementById("login-form");
var loginErrors = document.getElementById("login-errors");

for (var i = 0; i < openModalLinks.length; i++) {
  var modalLink = openModalLinks[i];

  modalLink.addEventListener("click", function (event) {
    event.preventDefault();

    var modalId = event.currentTarget.getAttribute("data-for");

    var modal = document.getElementById(modalId);
    modal.setAttribute("style", "display: block");
    overlay.setAttribute("style", "display: block");
  });
}

function closeModal(event) {
  var modal = event.currentTarget.parentElement;

  modal.removeAttribute("style");
  overlay.removeAttribute("style");
}

for (var j = 0; j < closeModalLinks.length; j++) {
  var closeModalLink = closeModalLinks[j];

  closeModalLink.addEventListener("click", closeModal);
}

if (loginForm) {
  loginForm.addEventListener("submit", function (event) {
    event.preventDefault();

    loginErrors.innerHTML = "";

    var formData = new FormData(loginForm);

    fetch(loginForm.action, {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest"
      }
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.success) {
          window.location.href = "/";
          return;
        }

        var errors = data.errors;

        for (var field in errors) {
          if (errors.hasOwnProperty(field)) {
            for (var i = 0; i < errors[field].length; i++) {
              var error = document.createElement("p");

              error.textContent = errors[field][i];
              error.className = "form-error";

              loginErrors.appendChild(error);
            }
          }
        }
      })
      .catch(function () {
        var error = document.createElement("p");

        error.textContent = "Произошла ошибка. Попробуйте ещё раз.";
        error.className = "form-error";

        loginErrors.appendChild(error);
      });
  });
}
