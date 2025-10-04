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
    console.log("Theme script from script.js loaded");

    function addEventListener() {
      //when clicked on rex-table-icon (a td) load the script
      document
        .querySelectorAll(".rex-link-expanded")
        .forEach(function (element) {
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

    addEventListener();

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
            console.log("Valid hex:", val);
            colorPickers[index].value = val;

            //remove red border from input
            hexInput.style.border = "";
            //remove invalid message if present
            if (
              hexInput.nextElementSibling &&
              hexInput.nextElementSibling.classList.contains("rex-js-message")
            ) {
              hexInput.nextElementSibling.remove();
            }
          } else {
            console.log("Invalid hex:", val);
            //add red border to input
            hexInput.style.border = "2px solid red";
            const invalidMessage = /*html*/ `
              <div class="rex-js-message rex-js-message-error" style="margin-top: 5px; padding: 5px;">
                Ungültiger Hex-Wert
              </div>
            `;
            //add message below input if not already present
            if (
              !hexInput.nextElementSibling ||
              !hexInput.nextElementSibling.classList.contains("rex-js-message")
            ) {
              hexInput.insertAdjacentHTML("afterend", invalidMessage);
            }
          }
        });
      });
    }
  });

  $(document).on("rex:ready", function () {
    // Code, der bei jedem Laden des Containers ausgeführt werden soll
    console.log("Theme script loaded");
    const wrapper = document.getElementById("theme-button-wrapper");
    console.log(wrapper);

    const toggleButton = document.getElementById("theme-toggle-button");
    console.log(toggleButton);
    const menu = document.getElementById("theme-menu");
    const chevron = document.getElementById("theme-chevron");
    const label = document.getElementById("theme-label");

    // Initiales Theme aus Cookie oder default
    let theme =
      document.cookie.replace(
        /(?:(?:^|.*;\s*)theme\s*=\s*([^;]*).*$)|^.*$/,
        "$1"
      ) || "light";
    document.documentElement.className = theme;
    if (label) label.textContent = theme;

    // Dropdown toggle
    toggleButton.addEventListener("click", (e) => {
      e.stopPropagation();
      menu.classList.toggle("open");
      chevron.classList.toggle("rotate-180");
      wrapper.classList.add("relative");
    });

    // Klick außerhalb
    document.addEventListener("click", () => {
      menu.classList.remove("open");
      chevron.classList.remove("rotate-180");
    });

    // Theme Auswahl
    wrapper.querySelectorAll(".theme-option").forEach((btn) => {
      btn.addEventListener("click", () => {
        theme = btn.dataset.theme;
        document.documentElement.className = theme;
        document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
        console.log("Theme set to:", theme);
        if (label) label.textContent = theme;
        menu.classList.remove("open");
        chevron.classList.remove("rotate-180");
        //find the :root --primary --secondary --accent colors and set them as css variables
        const rootStyles = getComputedStyle(document.documentElement);
        console.log(rootStyles);
        const primary = rootStyles.getPropertyValue("--primary");
        const secondary = rootStyles.getPropertyValue("--secondary");
        const accent = rootStyles.getPropertyValue("--accent");
        console.log("Primary: " + primary);
        console.log("Secondary: " + secondary);
        console.log("Accent: " + accent);
      });
    });
    console.log("moin");
  });
})(jQuery);

document.addEventListener("DOMContentLoaded", function () {});
