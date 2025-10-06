<?php

// Diese Datei ist keine Pflichdatei mehr.
// Die `boot.php` wird bei jeder Aktion in REDAXO ausgeführt (Frontend und Backend). Hier können beliebige Befehle ausgeführt werden.
// Dokumentation AddOn Aufbau und Struktur https://redaxo.org/doku/master/addon-struktur

$addon = rex_addon::get('demo_addon');

// Daten wie Autor, Version, Subpages etc. sollten wenn möglich in der `package.yml` notiert werden.
// https://redaxo.org/doku/master/eigenschaften#eigene_properties
// Sie können aber auch weiterhin hier gesetzt werden:
$addon->setProperty('author', 'Friends Of REDAXO');

// Die Datei sollte keine veränderbare Konfigurationen mehr enthalten, um die Updatefähigkeit zu erhalten.
// Stattdessen sollte dafür die `rex_config` verwendet werden (siehe `install.php`).
// Dokumentation Konfiguration https://www.redaxo.org/doku/master/konfiguration

// Klassen und lang-Dateien müssen hier nicht mehr eingebunden werden, sie werden nun automatisch gefunden.

// AddOn-Rechte (permissions) registieren
// Hinweis: In der `de_de.lang`-Datei sind Text-Einträge für das Backend vorhanden (z.B. perm_general_demo_addon[])
if (rex::isBackend() && is_object(rex::getUser())) {
    rex_perm::register('demo_addon[]');
    rex_perm::register('demo_addon[config]');
    rex_view::addCssFile($this->getAssetsUrl('css/output.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/alpine.js'));
    rex_view::addJsFile($this->getAssetsUrl('js/alpine2.js'));
    rex_view::addJsFile($this->getAssetsUrl('js/theme.js'));
    getStyles();

}

// Assets werden bei der Installation des AddOns in den assets-Ordner kopiert und stehen damit
// öffentlich zur Verfügung. Sie müssen dann allerdings noch eingebunden werden:

// Assets im Backend nur beim `demo_addon` einbinden.
// CSS und JavaScript-Dateien sollten nur im Backend eingebunden werden wenn sie benötigt werden.
// AddOn-Assets https://redaxo.org/doku/master/addon-assets
if (rex::isBackend() && rex::getUser() && 'demo_addon' == rex_be_controller::getCurrentPagePart(1)) {
    // Die `style.css` bei allen Pages und Subpages des AddOns im Backend einbinden
    rex_view::addCssFile($addon->getAssetsUrl('css/style.css'));

    // Die `script.js` bei allen Pages und Subpages des AddOns im Backend einbinden
    rex_view::addJsFile($addon->getAssetsUrl('js/script.js'), [rex_view::JS_IMMUTABLE => true]);

    // Die `eps.js` nur auf der Unterseite `eplist` des AddOns einbinden
    if ('eps' == rex_be_controller::getCurrentPagePart(2) && 'eplist' == rex_be_controller::getCurrentPagePart(3)) {
        rex_view::addJsFile($addon->getAssetsUrl('js/eps.js'), [rex_view::JS_IMMUTABLE => true]);
    }

    // JavaScript-Variable für das Backend im Head-Bereich setzen (var rex[])
    rex_view::setJsProperty('demo_addon_js', 'JS-Value demo_addon ...');
}

// Eigene PHP-Funktionen im Backend und Frontend einbinden
// PHP-Dateien mit eigenen Funktionen sollten im Ordner `functions` abgelegt werden
$addon->includeFile('functions/ep_functions.php');

// Include der Extensionpoint-PHP's im Verzeichnis `pages/extensionpoints/`
demo_addon_includeExtensionPoints();

// Eigene PHP-Funktionen einbinden, nur wenn im Backend eingeloggt
if (rex::isBackend() && rex::getUser()) {
    // Include der AddOn-Eigenen Dateien für das Backend
    //$addon->includeFile('functions/backend_functions.php');
}

// Falls eigene PHP-Funktionen nur für das Frontend benötigt werden, können diese hier eingebunden werden
if (rex::isFrontend()) {
    // Include der AddOn-Eigenen Dateien für das Frontend
    //$addon->includeFile('functions/frontend_functions.php');
}

function getStyles(){
    echo "<style>";
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT title, primary_color, secondary_color, accent_color FROM ' . rex::getTable('demo_addon_theme') . ' WHERE status=1 ORDER BY title ASC');
    $themes = [];
    for ($i = 0; $i < $sql->getRows(); $i++) {
        $title = strtolower(preg_replace('/\s+/', '-', $sql->getValue('title')));
        $themes[$title] = [
            'primary' => $sql->getValue('primary_color'),
            'secondary' => $sql->getValue('secondary_color'),
            'accent' => $sql->getValue('accent_color')
        ];
        //debug
        $sql->next();
    }
    ?>


<?php foreach ($themes as $class => $colors): ?>.<?=$class ?> {
--primary: <?=htmlspecialchars($colors['primary']) ?>;
--secondary: <?=htmlspecialchars($colors['secondary']) ?>;
--accent: <?=htmlspecialchars($colors['accent']) ?>;
}

<?php endforeach;
    echo "</style>";
    }
class MeinButton implements FriendsOfRedaxo\QuickNavigation\Button\ButtonInterface
{
    public $themes;
    private function registerThemes()
    {
        $themes = [];
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT title, primary_color, secondary_color, accent_color FROM ' . rex::getTable('demo_addon_theme') . ' WHERE status = 1 ORDER BY title ASC');
        // Hier können Sie Ihre Themen registrieren
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $themes[] = [
                'title' => $sql->getValue('title'),
                'primary_color' => $sql->getValue('primary_color'),
                'secondary_color' => $sql->getValue('secondary_color'),
                'accent_color' => $sql->getValue('accent_color'),
            ];
            $sql->next();
        }
        return $themes;
    }

    public function get(): string
    {
        // Logik für die Schaltfläche
        $html = <<<HTML
<div id="theme-button-wrapper" class="relative inline-block text-left">
    <div>
        <button id="theme-toggle-button" type="button"
            class="btn btn-default dropdown-toggle" onclick="test()">
            <span>Theme: <span id="theme-label"></span></span>
            <svg id="theme-chevron" class="-mr-1 ml-2 h-5 w-5 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div id="theme-menu" class="origin-top-right absolute quick-navigation-menu dropdown-menu dropdown-menu-right">
        <div class="py-1 bg-">
            <button class="theme-option px-4 py-2 block w-full text-left hover:bg-gray-100" data-theme="light">Helles Design</button>
            <button class="theme-option px-4 py-2  block w-full text-left hover:bg-gray-100" data-theme="dark">Dunkles Design</button>
            <button class="theme-option px-4 py-2  block w-full text-left hover:bg-gray-100" data-theme="custom">Custom Design (Grün)</button>
HTML;

$themes = $this->registerThemes();
foreach ($themes as $theme) {
    $themeClass = strtolower(preg_replace('/\s+/', '-', $theme['title']));
    $html .= <<<HTML
            <button class="theme-option px-4 py-2  block w-full text-left hover:bg-gray-100" data-theme="{$themeClass}">{$theme['title']}</button>
HTML;
}
$html .= <<<HTML
        </div>


    </div>
</div>

<script>

var listenerSet = false;
var themeCollapseIsOpen = false;
function test(){
    const wrapper = document.getElementById("theme-button-wrapper");

    console.log("moin");
    console.log(listenerSet);
    const chevron = document.getElementById("theme-chevron");
    chevron.classList.toggle("rotate-180");
    if(themeCollapseIsOpen) {
        console.log("closing");
        wrapper.classList.remove("open");
        open = false;
        return;
    }
    // if theme-menu is hidden, show it
    const menu = document.getElementById("theme-menu");
    menu.classList.toggle("open");
    
    console.log(menu);
    themeCollapseIsOpen = true;

    //theme selector listeners
    if(listenerSet) return;
    wrapper.classList.add("open");
    wrapper.querySelectorAll(".theme-option").forEach((btn) => {
        btn.addEventListener("click", () => {
            const theme = btn.dataset.theme;
            document.documentElement.className = theme;
            document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
            const label = document.getElementById("theme-label");
            if (label) label.textContent = theme;
            menu.classList.add("");
            wrapper.classList.remove("open");
            chevron.classList.remove("rotate-180");
            themeCollapseIsOpen = false;

        });
    });
    //add listener when clicked outside
    //

}
</script>
HTML;
//$html = "<button>test</button>"
        return $html ;
    }


}
use FriendsOfRedaxo\QuickNavigation\Button\ButtonRegistry;

ButtonRegistry::registerButton(new MeinButton(), 1);

if (rex::isBackend() && rex::getUser()) {
    rex_extension::register('PAGES_PREPARED', function ($ep) {
        $addon = rex_addon::get('demo_addon');
        $page = $addon->getProperty('page');
        $page['subpages']['theme-config'] = [
            'title' => 'Theme-Farben',
            'icon' => 'rex-icon fa-paint-brush',
        ];
        $addon->setProperty('page', $page);
    });
}