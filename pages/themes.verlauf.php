<?php

$addon = rex_addon::get('demo_addon');

// Parameter bereitstellen
$func = rex_request('func', 'string', ''); // Funktion: add/edit/delete/togglestatus
$id = rex_request('id', 'int', -1); // ID des Datensatzes
$oldstatus = rex_request('oldstatus', 'int', -1); // Alter Status beim Aktivieren/Deaktivieren

// Start der Liste für Pagination
$start = rex_request('start', 'int', -1);
if (-1 == $start) {
    $start = rex_request('Theme-Liste_start', 'int', 0);
}
$addon->setProperty('list_start', $start);

// SQL-Instanz bei vorhandener Funktion
if ($func) {
    $sql = rex_sql::factory();
    $sql->setDebug(false);
}

// Datensatz löschen
if ('delete' == $func) {
    $sql->setTable(rex::getTable('demo_addon_theme'));
    $sql->setWhere(['id' => $id]);
    $sql->delete();

    if (!$sql->hasError()) {
        echo rex_view::success($addon->i18n('list_deleted'));
    } else {
        echo rex_view::error($addon->i18n('list_error'));
        dump($sql->getError());
    }
    $func = '';
}

// Status toggeln
if ('togglestatus' == $func) {
    $status = (1 === $oldstatus) ? 0 : 1;

    $sql->setTable(rex::getTable('demo_addon_theme'));
    $sql->setWhere(['id' => $id]);
    $sql->setValue('status', $status);
    $sql->update();

    if (!$sql->hasError()) {
        if ($status) {
            echo rex_view::success($addon->i18n('list_activated'));
        } else {
            echo rex_view::success($addon->i18n('list_deactivated'));
        }
    } else {
        echo rex_view::error($addon->i18n('list_error'));
        dump($sql->getError());
    }
    $func = '';
}

// Datensatz erstellen oder bearbeiten
if (in_array($func, ['add', 'edit'])) {
    $title = 'edit' == $func ? $addon->i18n('list_edit') : $addon->i18n('list_create_new_entry');

    $form = rex_form::factory(
        rex::getTable('demo_addon_theme'),
        'Theme bearbeiten',
        'id=' . $id,
        'post',
        false
    );

    $form->addParam('id', $id);
    $form->addParam('sort', rex_request('sort', 'string', ''));
    $form->addParam('sorttype', rex_request('sorttype', 'string', ''));
    $form->addParam('start', rex_request('start', 'int', 0));

    // Theme Name
    $field = $form->addTextField('title');
    $field->setLabel('Theme-Name');
    $field->getValidator()->add('notEmpty', 'Der Theme-Name darf nicht leer sein.');

    // Primärfarbe
    $field = $form->addTextField('primary_color');
    $field->setLabel('Primärfarbe');
    $field->setAttribute('type', 'color');
    $field->getValidator()->add('notEmpty', 'Die Primärfarbe darf nicht leer sein.');
    // Textfeld für Hexcode
    $fieldText = $form->addTextField('primary_color_text');
    $fieldText->setLabel('Primärfarbe Hex-Code');
    // Sekundärfarbe
    $field = $form->addTextField('secondary_color');
    $field->setLabel('Sekundärfarbe');
    $field->setAttribute('type', 'color');
    $field->getValidator()->add('notEmpty', 'Die Sekundärfarbe darf nicht leer sein.');
    // Textfeld für Hexcode
    $fieldText = $form->addTextField('secondary_color_text');
    $fieldText->setLabel('Primärfarbe Hex-Code');
    // Akzentfarbe
    $field = $form->addTextField('accent_color');
    $field->setLabel('Akzentfarbe');
    $field->setAttribute('type', 'color');
    $field->getValidator()->add('notEmpty', 'Die Akzentfarbe darf nicht leer sein.');
    $fieldText = $form->addTextField('accent_color_text');
    $fieldText->setLabel('Primärfarbe Hex-Code');
    // Status
    $field = $form->addSelectField('status', null, ['class' => 'form-control selectpicker']);
    $field->setLabel('Status');
    $select = $field->getSelect();
    $select->addOption('Aktiv', 1);
    $select->addOption('Inaktiv', 0);

    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);

    echo $fragment->parse('core/page/section.php');

    exit;
}

// Liste der Themes anzeigen
$list = rex_list::factory(
    "
    SELECT `id`, `title`, `primary_color`, `secondary_color`, `accent_color`, `status`
    FROM " . rex::getTable('demo_addon_theme') . "
    ORDER BY `title` ASC
    ",
    30, // Anzahl der Einträge pro Seite
    'Theme-Liste',
    false
);

// Spaltenbreiten
$list->addTableColumnGroup([40, 200, 100, 100, 100, 80, 80]);

// Sortierbare Spalten
$list->setColumnSortable('id', 'asc');
$list->setColumnSortable('title', 'asc');

// Icon für neue Einträge und edit Icon
$thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey($addon->i18n('list_create_new_entry'), 'add') . ' title="' . $addon->i18n('list_create_new_entry') . '"><i class="rex-icon rex-icon-add"></i></a>';
$tdIcon = '<i class="rex-icon rex-icon-editmode" title="' . $addon->i18n('list_edit') . ' [###id###]"></i>';
$list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
$list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###', 'start' => $start]);

// Funktions-Spalte löschen
$list->addColumn('func', '', -1, ['<th>###VALUE###</th>', '<td nowrap="nowrap">###VALUE###</td>']);
$list->setColumnLabel('func', $addon->i18n('thead_func'));
$list->setColumnFormat('func', 'custom', static function ($params) use ($addon) {
    $start = $addon->getProperty('list_start');
    $list = $params['list'];
    $list->setColumnParams('delete', ['func' => 'delete', 'id' => '###id###', 'start' => $start]);
    $list->addLinkAttribute('delete', 'data-confirm', '[###title###] - ' . rex_addon::get('demo_addon')->i18n('confirm_delete'));
    return $list->getColumnLink('delete', '<i class="rex-icon rex-icon-delete"></i> ' . rex_addon::get('demo_addon')->i18n('delete'));
});

// Spaltenüberschriften
$list->setColumnLabel('id', $addon->i18n('thead_id'));
$list->setColumnLabel('title', 'Theme-Name');
$list->setColumnLabel('primary_color', 'Primärfarbe');
$list->setColumnLabel('secondary_color', 'Sekundärfarbe');
$list->setColumnLabel('accent_color', 'Akzentfarbe');
$list->setColumnLabel('status', $addon->i18n('thead_status'));

// Farben als Farbfelder anzeigen
$list->setColumnFormat('primary_color', 'custom', static function ($params) {
    $value = $params['list']->getValue('primary_color');
    return '<div style="width:24px; height:24px; background-color:' . $value . '; border: 1px solid #666;"></div>';
});
$list->setColumnFormat('secondary_color', 'custom', static function ($params) {
    $value = $params['list']->getValue('secondary_color');
    return '<div style="width:24px; height:24px; background-color:' . $value . '; border: 1px solid #666;"></div>';
});
$list->setColumnFormat('accent_color', 'custom', static function ($params) {
    $value = $params['list']->getValue('accent_color');
    return '<div style="width:24px; height:24px; background-color:' . $value . '; border: 1px solid #666;"></div>';
});

// Status als Link zum toggeln
$list->setColumnFormat('status', 'custom', static function ($params) use ($addon) {
    $start = $addon->getProperty('list_start');
    $list = $params['list'];
    $list->addLinkAttribute('status', 'class', 'toggle');
    $id = $list->getValue('id');
    $status = $list->getValue('status');
    $list->setColumnParams('status', ['func' => 'togglestatus', 'id' => $id, 'oldstatus' => $status, 'start' => $start]);
    if (1 == $status) {
        return $list->getColumnLink('status', '<span class="rex-online"><i class="rex-icon rex-icon-active-true"></i> Aktiv</span>');
    } else {
        return $list->getColumnLink('status', '<span class="rex-offline"><i class="rex-icon rex-icon-active-false"></i> Inaktiv</span>');
    }
});

// Keine Datensätze gefunden
$list->setNoRowsMessage($addon->i18n('list_no_rows'));

// Tabelle mit Stripe & Hover-Klassen
$list->addTableAttribute('class', 'table-striped table-hover');

$fragment = new rex_fragment();
$fragment->setVar('title', 'Themes verwalten');
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');
