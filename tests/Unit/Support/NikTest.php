<?php

use App\Support\Nik;

it('masks all but the last four digits', function () {
    expect(Nik::mask('3171234567890123'))->toBe('************0123');
});

it('normalises whitespace and separators', function () {
    expect(Nik::normalize(' 3171 2345.6789-0123 '))->toBe('3171234567890123');
});
