<?php

namespace Tests\Unit;

use App\Support\DepartmentResolver;
use Tests\TestCase;

class DepartmentResolverTest extends TestCase
{
    public function test_to_shortcut_accepts_shortcut_alias_and_long_name(): void
    {
        $this->assertSame('CCE', DepartmentResolver::toShortcut('CCE'));
        $this->assertSame('CCE', DepartmentResolver::toShortcut('bsit'));
        $this->assertSame('CCE', DepartmentResolver::toShortcut(config('school.departments.CCE')));
        $this->assertNull(DepartmentResolver::toShortcut('CITE'));
        $this->assertNull(DepartmentResolver::toShortcut(''));
    }

    public function test_options_use_official_shortcuts_as_values(): void
    {
        $options = DepartmentResolver::options();
        $values = array_column($options, 'value');

        $this->assertSame(DepartmentResolver::allShortcuts(), $values);
        $this->assertStringContainsString('CCE', $options[array_search('CCE', $values, true)]['label']);
    }

    public function test_equivalent_keys_include_shortcut_long_name_and_aliases(): void
    {
        $keys = DepartmentResolver::equivalentKeys('CCE');

        $this->assertContains('CCE', $keys);
        $this->assertContains(config('school.departments.CCE'), $keys);
        $this->assertContains('BSIT', $keys);
    }
}
