// https://stackoverflow.com/questions/50065773/what-is-the-best-solution-to-avoid-inline-onclick-function

var musicianRadio = document.getElementById("musician");
var managerRadio = document.getElementById("manager");
var technicianRadio = document.getElementById("technician");
var attribute = document.getElementById("attribute");

musicianRadio.addEventListener("click", showInstrument);
managerRadio.addEventListener("click", hide);
technicianRadio.addEventListener("click", showSpecialty);


function showInstrument() {
    attribute.style.visibility = "visible";
    attribute.placeholder = "Instrument";
}

function showSpecialty() {
    attribute.style.visibility = "visible";
    attribute.placeholder = "Specialty";
}

function hide() {
    attribute.style.visibility = "hidden";
}