<?php

declare(strict_types=1);

namespace App\Contracts;

interface WilayahSourceInterface
{
    /** @return iterable<array{code: string, name: string}> */
    public function provinces(): iterable;

    /** @return iterable<array{code: string, name: string, province_code: string}> */
    public function regencies(): iterable;

    /** @return iterable<array{code: string, name: string, regency_code: string}> */
    public function districts(): iterable;

    /** @return iterable<array{code: string, name: string, type: string, district_code: string}> */
    public function villages(): iterable;
}
