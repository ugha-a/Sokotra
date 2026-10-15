<?php

use App\Support\ProhibitedClaims;

it('finds prohibited phrases case-insensitively', function () {
    expect(ProhibitedClaims::find('Sinyal saya PASTI PROFIT dan tanpa risiko!'))
        ->toBe(['pasti profit', 'tanpa risiko']);
});

it('returns nothing for clean text', function () {
    expect(ProhibitedClaims::find('Analisa swing berbasis struktur pasar dan manajemen risiko.'))
        ->toBe([]);
});
