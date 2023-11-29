var deleteButton = document.getElementById("delete");
var confirmButton = document.getElementById("confirm-delete");

deleteButton.addEventListener("click", showConfirmButton);

function showConfirmButton() {
    confirmButton.style.visibility = "visible";
}