/*
Demo-AddOn
Diese JavaScript-Datei wird in der `boot.php` des AddOns `demo_addon` im Backend eingebunden (rex_view::addJsFile)
https://redaxo.org/doku/master/addon-assets
*/

// jQuery closure (»Funktionsabschluss«)
// Erzeugt einen Scope, also einen privaten Bereich
// http://molily.de/javascript-core/#closures
(function ($) {
  // rex:ready
  // Führt Code aus, sobald der DOM vollständig geladen wurde
  // https://redaxo.org/doku/master/addon-assets#rexready
  $(document).on("rex:ready", function (event, container) {
    // externe Links in neuem Fenster öffnen
    $('a[href^="http://"], a[href^="https://"]')
      .filter(function () {
        // filter out links that have the same domain name as the current page
        return this.hostname && this.hostname !== location.hostname;
      })
      // add a CSS class of "extern" to each external link (for styling)
      .addClass("extern")
      // inform visitor that link will open in new window
      .attr({
        target: "_blank",
        title: function () {
          return this.title + "";
        },
      });
  });
})(jQuery);

document.addEventListener("DOMContentLoaded", function () {
  console.log("Theme script from script.js loaded");

  function addEventListener() {
    //when clicked on rex-table-icon (a td) load the script
    document.querySelectorAll(".rex-link-expanded").forEach(function (element) {
      //first remove any existing event listeners
      element.addEventListener("click", function () {
        loadScript();
      });
    });
    //same for rex-icon-add
    document.querySelectorAll(".rex-icon-add").forEach(function (element) {
      element.addEventListener("click", function () {
        loadScript();
      });
    });
  }

  //add loadScript to all

  function loadScript() {
    const colorPickerPri = document.getElementById(
      "rex-demo-addon-theme-theme-bearbeiten-primary-color"
    );
    const hexInput = document.getElementById(
      "rex-demo-addon-theme-theme-bearbeiten-primary-color-text"
    );
    const colorPickerSec = document.getElementById(
      "rex-demo-addon-theme-theme-bearbeiten-secondary-color"
    );
    const hexInputSec = document.getElementById(
      "rex-demo-addon-theme-theme-bearbeiten-secondary-color-text"
    );
    const colorPickerAcc = document.getElementById(
      "rex-demo-addon-theme-theme-bearbeiten-accent-color"
    );
    const hexInputAcc = document.getElementById(
      "rex-demo-addon-theme-theme-bearbeiten-accent-color-text"
    );

    const colorPickers = [colorPickerPri, colorPickerSec, colorPickerAcc];
    const hexInputs = [hexInput, hexInputSec, hexInputAcc];
    //if any of the elements are null, return, calling function again
    if (
      !colorPickerPri ||
      !hexInput ||
      !colorPickerSec ||
      !hexInputSec ||
      !colorPickerAcc ||
      !hexInputAcc
    ) {
      console.log("Elements not found, retrying...");
      setTimeout(loadScript, 500);
      return;
    }
    console.log(colorPickers, hexInputs);
    //log all values
    console.log("Elements found, initializing...");
    console.log("Primary:", colorPickerPri.value, hexInput);
    console.log("Secondary:", colorPickerSec.value, hexInputSec);
    console.log("Accent:", colorPickerAcc.value, hexInputAcc);

    // Initial synchronisieren
    hexInput.value = colorPickers[0].value;
    hexInputSec.value = colorPickers[1].value;
    hexInputAcc.value = colorPickers[2].value;

    // Wenn Colorpicker ändert, update Textfeld
    colorPickers.forEach((picker, index) => {
      picker.addEventListener("input", () => {
        console.log("Picker changed:", picker.value);
        hexInputs[index].value = picker.value;
      });
    });

    // Wenn Textfeld ändert, update Colorpicker (mit Validierung)
    hexInputs.forEach((hexInput, index) => {
      hexInput.addEventListener("input", () => {
        const val = hexInput.value;
        console.log("Hex input changed:", val);
        if (/^#([0-9A-Fa-f]{3}){1,2}$/.test(val)) {
          colorPickers[index].value = val;
        }
      });
    });
  }
});
