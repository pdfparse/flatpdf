<?php

declare(strict_types=1);

/**
 * Demo: Generate a long PDF report with lots of table data.
 *
 * composer require pdfparse/flatpdf
 * php demo.php
 */

require_once __DIR__ . '/src/PdfDocument.php';
require_once __DIR__ . '/src/FontMetrics.php';
require_once __DIR__ . '/src/Style.php';
require_once __DIR__ . '/src/FlatPdf.php';

use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

// ─── Create a styled writer ─────────────────────────────────────────────

$style = Style::make(
    headerText: 'Acme Corp - Q4 2025 Financial Report',
    footerText: 'CONFIDENTIAL',
    tableHeaderBg: [0.18, 0.33, 0.59],
);

$pdf = FlatPdf::make($style);

// ─── Title Page ─────────────────────────────────────────────────────────

$pdf->space(180);
$pdf->h1('Q4 2025 Financial Report');
$pdf->space(10);
$pdf->text('Prepared by the Finance Department - January 2026');
$pdf->text('Acme Corporation | Internal Use Only');
$pdf->space(40);
$pdf->text(
    'This report contains quarterly financial data including revenue breakdowns by region, ' .
    'department operating expenses, employee headcount and compensation analysis, ' .
    'product-level profitability metrics, and year-over-year comparison data. ' .
    'All figures are in thousands of USD unless otherwise noted.'
);

// ─── Section 1: Revenue by Region ───────────────────────────────────────

$pdf->pageBreak();
$pdf->h1('1. Revenue by Region');
$pdf->text(
    'The following table shows revenue broken down by geographic region for each month of Q4. ' .
    'North America continues to lead, with APAC showing the strongest quarter-over-quarter growth at 12.4%.'
);
$pdf->space(8);

$headers = ['Region', 'October', 'November', 'December', 'Q4 Total', 'YoY Change'];
$regions = ['North America', 'Europe (EMEA)', 'Asia Pacific', 'Latin America', 'Middle East & Africa'];
$rows = [];

foreach ($regions as $region) {
    $oct = rand(800, 5000);
    $nov = rand(800, 5200);
    $dec = rand(900, 5500);
    $q4 = $oct + $nov + $dec;
    $yoy = rand(-5, 25);
    $rows[] = [
        $region,
        '$' . number_format($oct),
        '$' . number_format($nov),
        '$' . number_format($dec),
        '$' . number_format($q4),
        ($yoy >= 0 ? '+' : '') . $yoy . '%',
    ];
}

$pdf->table($headers, $rows, [
    'columnAligns' => ['left', 'right', 'right', 'right', 'right', 'right'],
]);

// ─── Section 2: Department Operating Expenses ───────────────────────────

$pdf->h1('2. Department Operating Expenses');
$pdf->text(
    'Detailed operating expenses by department and cost category. This table demonstrates ' .
    'handling of large datasets with automatic page breaks and repeated headers.'
);
$pdf->space(8);

$departments = [
    'Engineering', 'Product', 'Sales', 'Marketing', 'Finance', 'HR',
    'Legal', 'Operations', 'Customer Success', 'Data Science',
    'DevOps', 'QA', 'Design', 'IT', 'Facilities',
    'Executive', 'Research', 'Compliance', 'Training', 'Procurement',
];

$categories = ['Salaries', 'Benefits', 'Travel', 'Software', 'Equipment', 'Other'];

$headers2 = array_merge(['Department'], $categories, ['Total']);
$rows2 = [];

foreach ($departments as $dept) {
    $row = [$dept];
    $total = 0;
    foreach ($categories as $cat) {
        $val = rand(50, 2000);
        $row[] = '$' . number_format($val);
        $total += $val;
    }
    $row[] = '$' . number_format($total);
    $rows2[] = $row;
}

$pdf->table($headers2, $rows2, [
    'columnAligns' => array_merge(['left'], array_fill(0, count($categories) + 1, 'right')),
]);

// ─── Section 3: Employee Compensation (100 rows) ────────────────────────

$pdf->h1('3. Employee Compensation Analysis');
$pdf->text('Individual compensation data for the top 100 employees by total compensation.');
$pdf->space(8);

$firstNames = ['James', 'Mary', 'Robert', 'Patricia', 'John', 'Jennifer', 'Michael', 'Linda', 'David', 'Elizabeth',
    'William', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen'];
$lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
    'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
$titles = ['Engineer', 'Sr. Engineer', 'Staff Engineer', 'Manager', 'Sr. Manager', 'Director', 'VP', 'Principal', 'Lead', 'Architect'];
$levels = ['IC3', 'IC4', 'IC5', 'IC6', 'M3', 'M4', 'M5', 'IC7', 'IC5', 'IC6'];

$empHeaders = ['#', 'Name', 'Title', 'Level', 'Department', 'Base Salary', 'Bonus', 'Equity', 'Total Comp'];
$empRows = [];

for ($i = 1; $i <= 100; $i++) {
    $base = rand(85, 250) * 1000;
    $bonus = (int)($base * (rand(10, 30) / 100));
    $equity = rand(20, 150) * 1000;
    $total = $base + $bonus + $equity;
    $titleIdx = rand(0, count($titles) - 1);

    $empRows[] = [
        (string)$i,
        $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
        $titles[$titleIdx],
        $levels[$titleIdx],
        $departments[array_rand($departments)],
        '$' . number_format($base),
        '$' . number_format($bonus),
        '$' . number_format($equity),
        '$' . number_format($total),
    ];
}

$pdf->table($empHeaders, $empRows, [
    'columnAligns' => ['right', 'left', 'left', 'center', 'left', 'right', 'right', 'right', 'right'],
    'fontSize' => 7,
]);

// ─── Section 4: Product Profitability ───────────────────────────────────

$pdf->h1('4. Product-Level Profitability');
$pdf->text('Monthly P&L by product line showing revenue, COGS, gross margin, and operating margin.');
$pdf->space(8);

$products = [
    'Cloud Platform', 'API Gateway', 'Data Pipeline', 'Auth Service',
    'Analytics Dashboard', 'Mobile SDK', 'Enterprise Suite', 'Dev Tools',
    'Monitoring Pro', 'Security Scanner', 'CI/CD Pipeline', 'Edge CDN',
];

foreach ($products as $product) {
    $pdf->h3($product);
    $headers3 = ['Metric', 'October', 'November', 'December', 'Q4 Total'];
    $revenue = [rand(200, 800), rand(200, 850), rand(210, 900)];
    $cogs = array_map(fn($r) => (int)($r * (rand(20, 45) / 100)), $revenue);
    $gross = array_map(fn($r, $c) => $r - $c, $revenue, $cogs);
    $opex = array_map(fn($r) => (int)($r * (rand(15, 35) / 100)), $revenue);
    $opIncome = array_map(fn($g, $o) => $g - $o, $gross, $opex);

    $rows3 = [
        ['Revenue', '$' . $revenue[0], '$' . $revenue[1], '$' . $revenue[2], '$' . array_sum($revenue)],
        ['COGS', '$' . $cogs[0], '$' . $cogs[1], '$' . $cogs[2], '$' . array_sum($cogs)],
        ['Gross Profit', '$' . $gross[0], '$' . $gross[1], '$' . $gross[2], '$' . array_sum($gross)],
        [
            'Gross Margin',
            round($gross[0] / $revenue[0] * 100, 1) . '%',
            round($gross[1] / $revenue[1] * 100, 1) . '%',
            round($gross[2] / $revenue[2] * 100, 1) . '%',
            round(array_sum($gross) / array_sum($revenue) * 100, 1) . '%',
        ],
        ['Operating Expenses', '$' . $opex[0], '$' . $opex[1], '$' . $opex[2], '$' . array_sum($opex)],
        ['Operating Income', '$' . $opIncome[0], '$' . $opIncome[1], '$' . $opIncome[2], '$' . array_sum($opIncome)],
        [
            'Operating Margin',
            round($opIncome[0] / $revenue[0] * 100, 1) . '%',
            round($opIncome[1] / $revenue[1] * 100, 1) . '%',
            round($opIncome[2] / $revenue[2] * 100, 1) . '%',
            round(array_sum($opIncome) / array_sum($revenue) * 100, 1) . '%',
        ],
    ];

    $pdf->table($headers3, $rows3, [
        'columnAligns' => ['left', 'right', 'right', 'right', 'right'],
    ]);
}

// ─── Section 5: YoY Comparison ──────────────────────────────────────────

$pdf->h1('5. Year-over-Year Comparison');
$pdf->text('Key metrics comparison between Q4 2024 and Q4 2025 across all business units.');
$pdf->space(8);

$metrics = [
    'Total Revenue', 'Recurring Revenue', 'New Business Revenue', 'Expansion Revenue',
    'Gross Profit', 'Operating Income', 'EBITDA', 'Free Cash Flow',
    'Customer Count', 'New Customers', 'Churned Customers', 'Net Retention Rate',
    'ARR', 'Monthly Burn Rate', 'Runway (months)', 'CAC',
    'LTV', 'LTV:CAC Ratio', 'Payback Period (months)', 'Rule of 40 Score',
];

$yoyHeaders = ['Metric', 'Q4 2024', 'Q4 2025', 'Change', '% Change'];
$yoyRows = [];

foreach ($metrics as $metric) {
    $prev = rand(100, 5000);
    $change = rand(-15, 40);
    $curr = (int)($prev * (1 + $change / 100));
    $diff = $curr - $prev;
    $yoyRows[] = [
        $metric,
        '$' . number_format($prev) . 'K',
        '$' . number_format($curr) . 'K',
        ($diff >= 0 ? '+' : '') . '$' . number_format($diff) . 'K',
        ($change >= 0 ? '+' : '') . $change . '%',
    ];
}

$pdf->table($yoyHeaders, $yoyRows, [
    'columnAligns' => ['left', 'right', 'right', 'right', 'right'],
]);

// ─── Section 6: dataTable() with associative arrays ─────────────────────

$pdf->h1('6. Customer Accounts Detail');
$pdf->text('Top 40 customer accounts by annual contract value, with status and renewal dates.');
$pdf->space(8);

$companies = [
    'Acme Inc', 'GlobalTech', 'Nexus Systems', 'Peak Solutions', 'Vertex AI',
    'Summit Health', 'Atlas Group', 'Forge Labs', 'Cascade Networks', 'Horizon Digital',
    'Prime Dynamics', 'Nova Enterprises', 'Zenith Corp', 'Apex Software', 'Titan Industries',
    'Orbit Media', 'Flux Technologies', 'Spark Innovation', 'Core Logic', 'Bridge Analytics',
    'Vanguard Tech', 'Quantum Labs', 'Relay Systems', 'Beacon Health', 'Stratos Cloud',
    'Pinnacle Data', 'Trident Finance', 'Mosaic Corp', 'Keystone Inc', 'Helix Bio',
    'Prism Digital', 'Cobalt Solutions', 'Ember AI', 'Ridge Technologies', 'Crest Networks',
    'Pulse Media', 'Circuit Labs', 'Anchor Systems', 'Drift Analytics', 'Haven Corp',
];

$statuses = ['Active', 'At Risk', 'Expanding', 'New', 'Renewing'];
$tiers = ['Enterprise', 'Business', 'Startup'];

$customers = [];
for ($i = 0; $i < 40; $i++) {
    $customers[] = [
        'account' => $companies[$i],
        'tier' => $tiers[array_rand($tiers)],
        'status' => $statuses[array_rand($statuses)],
        'acv' => rand(25, 500) * 1000,
        'seats' => rand(10, 500),
        'renewal_date' => '2026-' . str_pad((string)rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad((string)rand(1, 28), 2, '0', STR_PAD_LEFT),
        'csm' => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
    ];
}

$pdf->dataTable($customers, [
    'columns' => ['account', 'tier', 'status', 'acv', 'seats', 'renewal_date', 'csm'],
    'columnLabels' => [
        'account' => 'Account Name',
        'tier' => 'Tier',
        'status' => 'Status',
        'acv' => 'ACV',
        'seats' => 'Seats',
        'renewal_date' => 'Renewal Date',
        'csm' => 'CSM',
    ],
    'formatters' => [
        'acv' => fn($val) => '$' . number_format((int)$val),
        'seats' => fn($val) => number_format((int)$val),
    ],
    'columnAligns' => ['left', 'center', 'center', 'right', 'right', 'center', 'left'],
]);

// ─── Section 7: Image Demo ───────────────────────────────────────────────

$pdf->pageBreak();
$pdf->h1('7. Image Embedding Demo');
$pdf->text('JPEG images can be embedded directly into the PDF using DCTDecode streams.');
$pdf->space(8);

// Create a small test JPEG in memory (a 100x60 red/blue gradient)
$testJpegPath = __DIR__ . '/test-image.jpg';
if (function_exists('imagecreatetruecolor')) {
    $img = imagecreatetruecolor(200, 120);
    for ($x = 0; $x < 200; $x++) {
        for ($y = 0; $y < 120; $y++) {
            $r = (int)(255 * $x / 200);
            $b = (int)(255 * $y / 120);
            imagesetpixel($img, $x, $y, imagecolorallocate($img, $r, 80, $b));
        }
    }
    imagejpeg($img, $testJpegPath, 85);
    imagedestroy($img);

    $pdf->text('Image from file path (200pt wide, left-aligned):');
    $pdf->space(4);
    $pdf->image($testJpegPath, width: 200);

    $pdf->text('Same image centered (150pt wide):');
    $pdf->space(4);
    $pdf->image($testJpegPath, width: 150, options: ['align' => 'center']);

    $pdf->text('Same image right-aligned (100pt wide):');
    $pdf->space(4);
    $pdf->image($testJpegPath, width: 100, options: ['align' => 'right']);

    $pdf->text('Image from raw string data:');
    $pdf->space(4);
    $pdf->imageFromString(file_get_contents($testJpegPath), width: 180);

    unlink($testJpegPath);
} else {
    $pdf->text('(GD extension not available - skipping image demo. Images still work with any JPEG file.)');
}

$pdf->space(12);
$pdf->text('Laravel/S3 usage example (not executed in demo):');
$pdf->code('$pdf->imageFromDisk(Storage::disk(\'s3\'), \'photos/logo.jpg\', width: 200);');

// ─── Save ───────────────────────────────────────────────────────────────

$outputPath = __DIR__ . '/demo-report.pdf';
$pdf->save($outputPath);

// Also generate an uncompressed version for size comparison
$styleUncompressed = new Style(
    headerText: 'Acme Corp - Q4 2025 Financial Report',
    footerText: 'CONFIDENTIAL',
    tableHeaderBg: [0.18, 0.33, 0.59],
    compress: false,
);
$pdfUncompressed = new FlatPdf($styleUncompressed);
$pdfUncompressed->h1('Compression comparison');
$pdfUncompressed->text('This PDF was generated without stream compression for size comparison.');
$pdfUncompressed->save(__DIR__ . '/demo-report-uncompressed.pdf');

echo "Generated: {$outputPath}\n";
echo "  Pages: {$pdf->getCurrentPage()}\n";
echo "  Compressed:   " . round(filesize($outputPath) / 1024) . " KB\n";
echo "  Uncompressed: " . round(filesize(__DIR__ . '/demo-report-uncompressed.pdf') / 1024) . " KB (1-page sample)\n";
