<?php

use FriendsOfRedaxo\MForm;

function addThemeField($settingsForm, $id)
{
    $settingsForm->addSelectField("$id.bg", array(
        'label' => 'Background',
        'options' => array(
            '' => 'None',
            'bg-white' => 'White',
            'bg-gray-100' => 'Gray 100',
            'bg-gray-200' => 'Gray 200',
            'bg-gray-300' => 'Gray 300',
            'bg-gradient-to-br from-[var(--secondary)] via-[var(--primary)] to-[var(--accent)] text-white' => 'Sec -> Prim -> Accent',
            'bg-gradient-to-br from-[var(--secondary)] to-[var(--accent)] text-white' => 'Sec -> Accent',
            'bg-gradient-to-br from-[var(--primary)] via-[var(--accent)] to-[var(--secondary)] text-white' => 'Prim -> Accent -> Sec',
            'bg-gradient-to-br from-[var(--primary)] to-[var(--secondary)] text-white' => 'Prim -> Sec',
            'bg-gradient-to-br from-[var(--accent)] to-[var(--primary)] text-white' => 'Accent -> Prim',
            'bg-gradient-to-br from-[var(--accent)] to-[var(--secondary)] text-white' => 'Accent -> Sec'
        )
    ));
    $settingsForm->addSelectField("$id.gradient_direction", array(
        'label' => 'Gradient Direction',
        'options' => [
            'bg-gradient-to-t' => '⬆️ Oben (to top)',
            'bg-gradient-to-tr' => '↗️ Oben-Rechts (to top right)',
            'bg-gradient-to-r' => '➡️ Rechts (to right)',
            'bg-gradient-to-br' => '↘️ Unten-Rechts (to bottom right)',
            'bg-gradient-to-b' => '⬇️ Unten (to bottom)',
            'bg-gradient-to-bl' => '↙️ Unten-Links (to bottom left)',
            'bg-gradient-to-l' => '⬅️ Links (to left)',
            'bg-gradient-to-tl' => '↖️ Oben-Links (to top left)',
        ]
    ));
    $settingsForm->addHtml('
        <div class="form-group">
            <div class="col-sm-2 control-label">
                <label class="control-label">Vorschau</label>
            </div>
            <div class="col-sm-10">
                <div id="gradient-preview" style="width:100%;height:60px;border-radius:8px;margin-top:10px;
                    background: linear-gradient(to bottom right, var(--secondary), var(--primary), var(--accent));">
                </div>
            </div>
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const preview = document.getElementById("gradient-preview");
            // Finde die Selects dynamisch per Namenskonvention!
            const bgSelect = document.querySelector(\'select[name$="[bg]"]\');
            const dirSelect = document.querySelector(\'select[name$="[gradient_direction]"]\');
    
            function updateGradientPreview() {
                const bgVal = bgSelect?.value || "";
                const dirVal = dirSelect?.value || "bg-gradient-to-br";
    
                // Mapping für Richtung
                const dirMap = {
                    "t": "to top",
                    "tr": "to top right",
                    "r": "to right",
                    "br": "to bottom right",
                    "b": "to bottom",
                    "bl": "to bottom left",
                    "l": "to left",
                    "tl": "to top left"
                };
    
                // Standardwerte
                let direction = "to bottom right";
                // Richtung aus gradient_direction select extrahieren:
                if(dirVal.startsWith("bg-gradient-to-")) {
                    let key = dirVal.replace("bg-gradient-to-", "");
                    direction = dirMap[key] || direction;
                }
    
                // Default-Farben als CSS-Variablen
                let colors = "var(--secondary), var(--primary), var(--accent)";
    
                // Wenn BG ein Gradient ist, dann ggf. via/zu Farben auflösen:
                if(bgVal.includes("gradient")) {
                    // direction immer nach gradient_direction-Select
                    // Farben via Tailwind-like-Pattern extrahieren:
                    // z.B. bg-gradient-to-br from-[var(--secondary)] via-[var(--primary)] to-[var(--accent)]
                    // RegEx für Farben
                    const from = bgVal.match(/from-\[([^\]]+)\]/)?.[1];
                    const via = bgVal.match(/via-\[([^\]]+)\]/)?.[1];
                    const to = bgVal.match(/to-\[([^\]]+)\]/)?.[1];
                    if(from && via && to) colors = `${from}, ${via}, ${to}`;
                    else if(from && to) colors = `${from}, ${to}`;
                }
    
                // Den Background setzen
                preview.style.background = `linear-gradient(${direction}, ${colors})`;
            }
    
            // EventListener für beide Selects
            [bgSelect, dirSelect].forEach(sel => {
                if(sel){
                    sel.addEventListener("change", updateGradientPreview);
                }
            });
            // Initial setzen
            updateGradientPreview();
        });
        </script>
    ');
    return $settingsForm;
}
