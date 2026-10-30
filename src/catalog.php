<?php
/**
 * Empfohlener Grundvorrat nach BBK-Ratgeber „Katastrophenalarm – Ratgeber für Notfallvorsorge“.
 *
 * Mengen gelten für 1 Person und 10 Tage (scale = person), pro Person ohne Tagesbezug
 * (scale = head), pro Haustier und 10 Tage (scale = pet) oder pauschal pro Haushalt (scale = fixed).
 * Nährwerte sind typische Durchschnittswerte je 100 g bzw. 100 ml, keine Herstellerangaben.
 */

return [
    'categories' => [
        // slug, name, BBK-Richtwert (Text), sort
        ['getreide',    'Getreide, Brot, Kartoffeln', '3,5 kg', 10],
        ['gemuese',     'Gemüse, Hülsenfrüchte',      '4,0 kg', 20],
        ['obst',        'Obst, Nüsse',                '2,5 kg', 30],
        ['milch',       'Milch, Milchprodukte',       '2,6 kg', 40],
        ['eiweiss',     'Fisch, Fleisch, Eier',       '1,5 kg', 50],
        ['fette',       'Fette, Öle',                 '0,36 kg', 60],
        ['getraenke',   'Getränke',                   '20 l', 110],
        ['sonstiges',   'Sonstiges',                  'nach Belieben', 80],
        ['ausruestung', 'Notfall & Hygiene',          'Checkliste', 90],
        ['haustiere',   'Haustiere',                  'pro Tier', 100],
    ],

    // category, name, unit, base qty, scale, pack size, pack label, kcal, protein, fat, carbs, shelf life (months)
    'items' => [
        ['getreide', 'Knäckebrot',                 'kg', 0.5,  'person', 0.25, 'Packung 250 g',  335, 9.5, 1.5, 66, 12],
        ['getreide', 'Nudeln',                     'kg', 1.0,  'person', 0.5,  'Packung 500 g',  355, 12.5, 1.8, 70, 24],
        ['getreide', 'Reis',                       'kg', 0.75, 'person', 1.0,  'Packung 1 kg',   350, 7.0, 0.6, 77, 24],
        ['getreide', 'Haferflocken',               'kg', 0.5,  'person', 0.5,  'Packung 500 g',  370, 13.5, 7.0, 59, 12],
        ['getreide', 'Kartoffeln',                 'kg', 0.75, 'person', 2.5,  'Netz 2,5 kg',     72, 2.0, 0.1, 15.6, 4],

        ['gemuese',  'Erbsen & Möhren (Dose)',     'kg', 1.0,  'person', 0.28, 'Dose 400 g',      50, 3.0, 0.4, 7.0, 36],
        ['gemuese',  'Weiße Bohnen (Dose)',        'kg', 0.8,  'person', 0.25, 'Dose 400 g',      95, 6.5, 0.5, 13, 36],
        ['gemuese',  'Tomaten, gehackt (Dose)',    'kg', 0.8,  'person', 0.4,  'Dose 400 g',      22, 1.1, 0.2, 3.6, 24],
        ['gemuese',  'Linsen, getrocknet',         'kg', 0.5,  'person', 0.5,  'Packung 500 g',  325, 24, 1.5, 48, 24],
        ['gemuese',  'Rotkohl (Glas)',             'kg', 0.5,  'person', 0.68, 'Glas 720 ml',     55, 1.2, 0.2, 11, 24],
        ['gemuese',  'Mais (Dose)',                'kg', 0.4,  'person', 0.285,'Dose 340 g',      80, 2.9, 1.2, 14, 36],

        ['obst',     'Obstkonserven',              'kg', 1.5,  'person', 0.25, 'Dose 425 ml',     65, 0.5, 0.1, 15, 24],
        ['obst',     'Apfelmus (Glas)',            'kg', 0.4,  'person', 0.71, 'Glas 720 ml',     75, 0.3, 0.2, 17, 18],
        ['obst',     'Trockenobst',                'kg', 0.3,  'person', 0.2,  'Beutel 200 g',   290, 2.5, 0.5, 66, 12],
        ['obst',     'Nüsse',                      'kg', 0.3,  'person', 0.2,  'Beutel 200 g',   640, 15, 60, 8, 9],

        ['milch',    'H-Milch',                    'l',  2.0,  'person', 1.0,  'Tetrapak 1 l',    64, 3.3, 3.5, 4.8, 3],
        ['milch',    'Hartkäse',                   'kg', 0.3,  'person', 0.2,  'Stück 200 g',    390, 27, 31, 0, 4],
        ['milch',    'Kondensmilch',               'kg', 0.3,  'person', 0.34, 'Dose 340 g',     130, 6.5, 7.5, 9.5, 12],

        ['eiweiss',  'Thunfisch (Dose)',           'kg', 0.5,  'person', 0.15, 'Dose 185 g',     110, 25, 1.0, 0, 48],
        ['eiweiss',  'Würstchen / Fleischkonserve','kg', 0.6,  'person', 0.25, 'Glas 250 g',     260, 13, 23, 1, 24],
        ['eiweiss',  'Sardinen in Öl',             'kg', 0.4,  'person', 0.09, 'Dose 125 g',     220, 24, 14, 0, 48],

        ['fette',    'Rapsöl',                     'l',  0.25, 'person', 1.0,  'Flasche 1 l',    820, 0, 92, 0, 18],
        ['fette',    'Butterschmalz',              'kg', 0.11, 'person', 0.25, 'Glas 250 g',     900, 0.3, 99.5, 0, 12],

        ['getraenke','Trinkwasser',                'l',  18,   'person', 9.0,  'Kiste 6 × 1,5 l',  0, 0, 0, 0, 24],
        ['getraenke','Fruchtsaft',                 'l',  2,    'person', 1.0,  'Flasche 1 l',     45, 0.4, 0.1, 10, 12],

        ['sonstiges','Zucker',                     'kg', 0.25, 'person', 1.0,  'Packung 1 kg',   400, 0, 0, 100, 60],
        ['sonstiges','Honig',                      'kg', 0.25, 'person', 0.5,  'Glas 500 g',     305, 0.4, 0, 75, 24],
        ['sonstiges','Salz',                       'kg', 0.1,  'person', 0.5,  'Packung 500 g',    0, 0, 0, 0, 60],
        ['sonstiges','Schokolade',                 'kg', 0.2,  'person', 0.1,  'Tafel 100 g',    535, 7, 31, 55, 12],
        ['sonstiges','Eintopf (Dose)',             'kg', 0.8,  'person', 0.8,  'Dose 800 g',      75, 4.0, 2.5, 9, 36],

        ['ausruestung','Toilettenpapier (Rollen)', 'stk', 3,   'person', 8,    'Packung 8 Rollen', 0, 0, 0, 0, 0],
        ['ausruestung','Taschenlampe',             'stk', 1,   'head',   1,    'Stück',            0, 0, 0, 0, 0],
        ['ausruestung','Kurbel- oder Batterieradio','stk',1,   'fixed',  1,    'Stück',            0, 0, 0, 0, 0],
        ['ausruestung','Batterien (AA)',           'stk', 12,  'fixed',  4,    'Packung 4 Stück',  0, 0, 0, 0, 60],
        ['ausruestung','Kerzen / Teelichter',      'stk', 20,  'fixed',  10,   'Packung 10 Stück', 0, 0, 0, 0, 0],
        ['ausruestung','Streichhölzer / Feuerzeug','stk', 2,   'fixed',  1,    'Stück',            0, 0, 0, 0, 0],
        ['ausruestung','Campingkocher',            'stk', 1,   'fixed',  1,    'Stück',            0, 0, 0, 0, 0],
        ['ausruestung','Gaskartuschen',            'stk', 4,   'fixed',  1,    'Stück',            0, 0, 0, 0, 96],
        ['ausruestung','Erste-Hilfe-Set',          'stk', 1,   'fixed',  1,    'Stück',            0, 0, 0, 0, 60],
        ['ausruestung','Müllbeutel',               'stk', 20,  'fixed',  20,   'Rolle 20 Stück',   0, 0, 0, 0, 0],

        ['haustiere','Tierfutter',                 'kg', 1.5,  'pet',    1.0,  'Packung 1 kg',   350, 25, 12, 40, 12],
        ['haustiere','Wasser für Tiere',           'l',  5,    'pet',    9.0,  'Kiste 6 × 1,5 l',  0, 0, 0, 0, 24],
    ],

    // Verpackungsform für die Regalzeichnung
    'shapes' => [
        'Knäckebrot' => 'box', 'Nudeln' => 'bag', 'Reis' => 'bag', 'Haferflocken' => 'box', 'Kartoffeln' => 'sack',
        'Erbsen & Möhren (Dose)' => 'can', 'Weiße Bohnen (Dose)' => 'can', 'Tomaten, gehackt (Dose)' => 'can',
        'Linsen, getrocknet' => 'bag', 'Rotkohl (Glas)' => 'jar', 'Mais (Dose)' => 'can',
        'Obstkonserven' => 'can', 'Apfelmus (Glas)' => 'jar', 'Trockenobst' => 'bag', 'Nüsse' => 'bag',
        'H-Milch' => 'carton', 'Hartkäse' => 'block', 'Kondensmilch' => 'can',
        'Thunfisch (Dose)' => 'tin', 'Würstchen / Fleischkonserve' => 'jar', 'Sardinen in Öl' => 'tin',
        'Rapsöl' => 'bottle', 'Butterschmalz' => 'jar',
        'Trinkwasser' => 'crate', 'Fruchtsaft' => 'bottle',
        'Zucker' => 'box', 'Honig' => 'jar', 'Salz' => 'box', 'Schokolade' => 'bar', 'Eintopf (Dose)' => 'can',
        'Toilettenpapier (Rollen)' => 'roll', 'Taschenlampe' => 'torch', 'Kurbel- oder Batterieradio' => 'radio',
        'Batterien (AA)' => 'battery', 'Kerzen / Teelichter' => 'candle', 'Streichhölzer / Feuerzeug' => 'box',
        'Campingkocher' => 'stove', 'Gaskartuschen' => 'can', 'Erste-Hilfe-Set' => 'kit', 'Müllbeutel' => 'roll',
        'Tierfutter' => 'sack', 'Wasser für Tiere' => 'crate',
    ],
];
