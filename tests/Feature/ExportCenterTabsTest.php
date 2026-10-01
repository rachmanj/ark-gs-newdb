<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class ExportCenterTabsTest extends TestCase
{
    public function test_export_center_index_renders_ark_and_sap_module_tabs(): void
    {
        $user = new User([
            'name' => 'Export Center Tester',
            'project_code' => 'TST',
        ]);
        $user->id = 1;
        $this->actingAs($user);

        $arkModules = [
            ['code' => 'po', 'label' => 'PO With ETA'],
            ['code' => 'grpo', 'label' => 'GRPO'],
        ];
        $sapModules = [
            ['code' => 'sap01', 'label' => 'Purchase Order Complete (01)'],
            ['code' => 'sap10', 'label' => 'Stock Report (10)'],
        ];

        $html = view('export-center.index', compact('arkModules', 'sapModules'))->render();

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $tabLinks = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' nav-link ') and @data-toggle='tab']");
        $this->assertCount(2, $tabLinks);

        $this->assertNotNull($dom->getElementById('tab-ark'));
        $this->assertNotNull($dom->getElementById('tab-sap'));

        $arkPoCheckbox = $xpath->query("//div[@id='tab-ark']//input[@type='checkbox' and @id='module-po']");
        $this->assertCount(1, $arkPoCheckbox);

        $arkGrpoCheckbox = $xpath->query("//div[@id='tab-ark']//input[@type='checkbox' and @id='module-grpo']");
        $this->assertCount(1, $arkGrpoCheckbox);

        $sap01Checkbox = $xpath->query("//div[@id='tab-sap']//input[@type='checkbox' and @id='module-sap01']");
        $this->assertCount(1, $sap01Checkbox);

        $sap10Checkbox = $xpath->query("//div[@id='tab-sap']//input[@type='checkbox' and @id='module-sap10']");
        $this->assertCount(1, $sap10Checkbox);

        $poOutsideArk = $xpath->query("//div[@id='tab-sap']//input[@id='module-po']");
        $this->assertCount(0, $poOutsideArk);

        $sap01OutsideSap = $xpath->query("//div[@id='tab-ark']//input[@id='module-sap01']");
        $this->assertCount(0, $sap01OutsideSap);

        $arkChecked = $xpath->query("//div[@id='tab-ark']//input[@type='checkbox' and @name='modules[]' and @checked]");
        $this->assertCount(0, $arkChecked, 'No ARK-GS module checkbox should be checked on initial load.');

        $sapChecked = $xpath->query("//div[@id='tab-sap']//input[@type='checkbox' and @name='modules[]' and @checked]");
        $this->assertCount(0, $sapChecked, 'No SAP report module checkbox should be checked on initial load.');

        $arkCountEl = $dom->getElementById('export-tab-ark-count');
        $this->assertNotNull($arkCountEl);
        $this->assertSame('(0)', trim($arkCountEl->textContent));

        $sapCountEl = $dom->getElementById('export-tab-sap-count');
        $this->assertNotNull($sapCountEl);
        $this->assertSame('(0)', trim($sapCountEl->textContent));
    }
}
