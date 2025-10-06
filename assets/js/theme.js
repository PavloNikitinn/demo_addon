document.addEventListener("DOMContentLoaded", () => {
  console.log("Theme script loaded");
  const wrapper = document.getElementById("theme-button-wrapper");
  console.log(wrapper);

  const toggleButton = document.getElementById("theme-toggle-button");
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
    console.log("Toggle theme menu");
    menu.classList.toggle("");
    chevron.classList.toggle("rotate-180");
    wrapper.classList.toggle("show");
    wrapper.classList.add("show");
  });

  // Klick außerhalb
  document.addEventListener("click", () => {
    menu.classList.add("");
    console.log("Toggle theme menu close");
    chevron.classList.remove("rotate-180");
  });

  // Theme Auswahl
  wrapper.querySelectorAll(".theme-option").forEach((btn) => {
    btn.addEventListener("click", () => {
      theme = btn.dataset.theme;
      console.log("Toggle theme to:", theme);

      document.documentElement.className = theme;
      document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
      if (label) label.textContent = theme;
      menu.classList.add("");
      chevron.classList.remove("rotate-180");
    });
  });
});
