<?php
session_start(); // Start the session

// --- Data Tables ---
$rateTable = [
    'Manager' => 500,
    'Supervisor' => 400,
    'Employee' => 300,
];

// Threshold => Rate (Highest applicable rate applies)
$bonusTable = [
    0 => 0,         // Base case
    3000 => 0.15,
    5000 => 0.20,
    10000 => 0.25,
    15000 => 0.30,
];

// Threshold => Rate (Highest applicable rate applies)
$taxTable = [
    0 => 0,         // Base case
    2000 => 0.18,
    4000 => 0.23,
    8000 => 0.25,
    15000 => 0.32,
];

// --- Initialize Session Data ---
if (!isset($_SESSION['employees'])) {
    $_SESSION['employees'] = [];
}

// --- Initialize Message Variables ---
$message = '';
$message_type = 'info'; // Default type: 'info', 'success', 'error'

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- Add Employee Action ---
    if (isset($_POST['addEmployee'])) {
        $lastName = trim($_POST['lastName']);
        $firstName = trim($_POST['firstName']);
        $position = $_POST['position'] ?? '';
        $hrsWorkInput = filter_input(INPUT_POST, 'hrsWork', FILTER_VALIDATE_FLOAT); // Allow decimals

        // Validation
        $errors = [];
        if (empty($lastName)) $errors[] = "Last Name is required.";
        if (empty($firstName)) $errors[] = "First Name is required.";
        if (empty($position) || !isset($rateTable[$position])) $errors[] = "Valid Position must be selected.";
        if ($hrsWorkInput === false || $hrsWorkInput <= 0) $errors[] = "Hours Worked must be a positive number.";

        if (empty($errors)) {
             // Add employee to the session array
             $_SESSION['employees'][] = [
                 'lastName' => htmlspecialchars($lastName), // Sanitize for output
                 'firstName' => htmlspecialchars($firstName), // Sanitize for output
                 'position' => $position,
                 'hrsWork' => $hrsWorkInput,
             ];
             $message = "Employee " . htmlspecialchars($firstName) . " " . htmlspecialchars($lastName) . " added successfully.";
             $message_type = 'success';
         } else {
             // Build error message list
             $errorMessage = "Failed to add employee. Please fix the following:<ul>";
             foreach ($errors as $error) {
                 $errorMessage .= "<li>" . htmlspecialchars($error) . "</li>";
             }
             $errorMessage .= "</ul>";
             $message = $errorMessage;
             $message_type = 'error';
         }

    // --- Reset Action ---
    } elseif (isset($_POST['reset'])) {
        // Clear employee data from session
        $_SESSION['employees'] = [];
        $message = "Payroll data has been reset.";
        $message_type = 'info';

    // --- Logout Action ---
    } elseif (isset($_POST['logout'])) {
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
        // IMPORTANT: Redirect to your actual login page.
        // Create a 'login.php' or change this path.
        header("Location: login.php"); // Redirect to login page
        exit(); // Terminate script execution after redirect
    }
}

// --- Payroll Calculation Function ---
function calculatePayroll($employees, $rateTable, $bonusTable, $taxTable) {
    $payroll = [];

    // Sort bonus/tax tables descending by threshold once before the loop
    krsort($bonusTable);
    krsort($taxTable);

    foreach ($employees as $index => $employee) {
        $position = $employee['position'];
        $hrsWork = $employee['hrsWork'];
        $rate = $rateTable[$position] ?? 0;
        $gross = $rate * $hrsWork;

        // Calculate bonus (highest applicable threshold)
        $applicableBonusRate = 0;
        foreach ($bonusTable as $threshold => $percentage) {
            if ($gross >= $threshold) {
                $applicableBonusRate = $percentage;
                break; // Found the highest applicable rate
            }
        }
        $bonusAmount = $gross * $applicableBonusRate;

        // Calculate tax (highest applicable threshold)
        $applicableTaxRate = 0;
         foreach ($taxTable as $threshold => $percentage) {
            if ($gross >= $threshold) {
                $applicableTaxRate = $percentage;
                break;
            }
        }
        $taxAmount = $gross * $applicableTaxRate;

        // Calculate other deductions
        // Note: These are simplified calculations based on example.
        // Real SSS/Pag-Ibig are often tiered or have contribution caps.
        $sss = $gross * 0.03;      // Example: 3% of gross
        $pagibig = $gross * 0.02;  // Example: 2% of gross (adjust as needed)

        // Calculate total deductions
        $totalDeduction = $sss + $pagibig + ($taxAmount > 0 ? $taxAmount : 0);
        // Calculate net pay
        $netpay = $gross + ($bonusAmount > 0 ? $bonusAmount : 0) - $totalDeduction;

        // Store detailed payroll data for the table
        $payroll[] = [
            'SN' => $index + 1,
            'Last Name' => $employee['lastName'], // Already sanitized on input
            'First Name' => $employee['firstName'], // Already sanitized on input
            'Position' => $employee['position'],
            'Rate' => $rate,
            'Hrs Work' => $hrsWork,
            'Gross' => $gross,
            'Bonus' => ($bonusAmount > 0) ? $bonusAmount : '-', // Use '-' for display if zero
            'SSS' => $sss,
            'Tax' => ($taxAmount > 0) ? $taxAmount : '-',    // Use '-' for display if zero
            'PagIbig' => $pagibig,
            'Total Deduction' => $totalDeduction,
            'Netpay' => $netpay,
        ];
    }

    // Restore original order of bonus/tax tables (optional, good practice)
    ksort($bonusTable);
    ksort($taxTable);

    return $payroll;
}

// --- Calculate Payroll Data (if employees exist) ---
$payrollData = [];
if (!empty($_SESSION['employees'])) {
    $payrollData = calculatePayroll($_SESSION['employees'], $rateTable, $bonusTable, $taxTable);
}

// --- Currency Formatting Function ---
function formatCurrency($amount) {
    if (!is_numeric($amount)) {
        return $amount; // Return non-numeric values (like '-') as is
    }
    // Format with Philippine Peso sign, 2 decimal places, comma separator
    return '₱ ' . number_format($amount, 2, '.', ',');
}

// --- Function to display value or '-' ---
function displayValue($value) {
     if ($value === '-') {
        return '-'; // Return '-' directly
     }
     // Check if it's numeric before formatting (handles SSS, PagIbig etc.)
     if (is_numeric($value)) {
         return formatCurrency($value); // Format numeric values as currency
     }
     // For non-numeric, non-'-' values (like names, position), sanitize just in case
     // (although names are already sanitized on input)
     return htmlspecialchars($value);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Payroll System</title>
    <!-- Link Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- Global Styles & Resets --- */
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-gray: #f8f9fa;
            --medium-gray: #dee2e6;
            --dark-gray: #343a40;
            --border-color: #ced4da;
            --text-color: #212529;
            --card-bg: #ffffff;
            --body-bg: #f4f6f9;
            --border-radius: 0.3rem;
            --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-color);
            background-color: var(--body-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* --- Layout Containers --- */
        .page-wrapper {
            max-width: 1400px; /* Wider container for large tables */
            margin: 2rem auto;
            background-color: transparent; /* Wrapper is just for centering */
            padding: 0 1rem; /* Add padding on smaller screens */
        }
        .main-header {
            background-color: var(--card-bg);
            color: var(--primary-color);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: var(--border-radius) var(--border-radius) 0 0; /* Rounded top corners */
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem; /* Space below header */
        }
        .logo { font-weight: 700; font-size: 1.5rem; }
        .header-right { display: flex; align-items: center; }
        .content-area { padding: 0; /* Padding handled by cards */ }
        .main-footer {
            text-align: center;
            padding: 1rem;
            margin-top: 1.5rem;
            background-color: var(--light-gray);
            color: var(--secondary-color);
            font-size: 0.875em;
            border-top: 1px solid var(--medium-gray);
             border-radius: 0 0 var(--border-radius) var(--border-radius); /* Rounded bottom corners */
        }
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--box-shadow);
        }
        .section-title {
            margin-top: 0;
            margin-bottom: 1.25rem;
            font-weight: 600;
            color: var(--dark-gray);
            font-size: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--medium-gray);
        }

        /* --- Message Styling --- */
        .message {
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            border-radius: var(--border-radius);
            border: 1px solid transparent;
            font-size: 0.95em;
        }
        .message-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .message-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .message-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
        .message-error ul { margin-top: 0.5rem; margin-bottom: 0; padding-left: 1.25rem; }
        .message-error li { margin-bottom: 0.25rem; }

        /* --- Form Styling --- */
        .employee-form .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Responsive columns */
            gap: 1rem 1.5rem; /* Row and column gap */
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 500;
            font-size: 0.9em;
            color: var(--dark-gray);
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95em;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .form-group input::placeholder { color: #aaa; }

        /* --- Button Styling --- */
        .form-actions { display: flex; gap: 0.75rem; justify-content: flex-start; margin-top: 1rem; }
        .button {
            border: none;
            padding: 0.6rem 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.95em;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 500;
            text-decoration: none; /* For potential link buttons */
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
            line-height: 1.5; /* Ensure consistent height */
        }
        .button svg { width: 18px; height: 18px; vertical-align: middle; }

        .button-primary { background-color: var(--primary-color); color: white; }
        .button-primary:hover { background-color: #0056b3; }

        .button-secondary { background-color: var(--secondary-color); color: white; }
        .button-secondary:hover { background-color: #5a6268; }

        .button-logout { background-color: var(--danger-color); color: white; padding: 0.5rem 0.8rem; font-size: 0.9em; }
        .button-logout:hover { background-color: #c82333; }
        .logout-form { margin: 0; /* Reset default form margin */ display: inline-block; }

        /* --- Table Styling --- */
        .table-responsive { overflow-x: auto; /* Essential for responsiveness */ }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0; /* Margin handled by card */
            font-size: 0.9em;
            background-color: var(--card-bg);
        }
        th, td {
            border: 1px solid var(--medium-gray);
            padding: 0.6rem 0.75rem;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap; /* Prevent wrapping, rely on scroll */
        }
        th {
            background-color: var(--light-gray);
            font-weight: 600; /* Semibold for headers */
            color: var(--dark-gray);
            position: sticky; /* Make header sticky during vertical scroll if table is long */
            top: 0;
            z-index: 1;
        }
        tbody tr:nth-child(even) { background-color: #f8f8f8; } /* Subtle striping */
        tbody tr:hover { background-color: #e9ecef; } /* Hover effect */

        /* Alignment & Specific Column Styles */
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        th:nth-child(1), td:nth-child(1) { width: 3%; } /* SN */
        th:nth-child(2), td:nth-child(2) { width: 10%; } /* Last Name */
        th:nth-child(3), td:nth-child(3) { width: 10%; } /* First Name */
        th:nth-child(4), td:nth-child(4) { width: 8%; } /* Position */
        th:nth-child(5), td:nth-child(5) { width: 7%; }  /* Rate */
        th:nth-child(6), td:nth-child(6) { width: 6%; }  /* Hrs Work */
        th:nth-child(7), td:nth-child(7) { width: 9%; } /* Gross */
        th:nth-child(8), td:nth-child(8) { width: 8%; }  /* Bonus */
        th:nth-child(9), td:nth-child(9) { width: 7%; }  /* SSS */
        th:nth-child(10), td:nth-child(10){ width: 8%; } /* Tax */
        th:nth-child(11), td:nth-child(11){ width: 7%; } /* Pagibig */
        th:nth-child(12), td:nth-child(12){ width: 9%;} /* Deductions */
        th:nth-child(13), td:nth-child(13){ width: 9%; } /* Netpay */

        .net-pay-header, .net-pay { font-weight: bold; }
        tfoot td { background-color: #e9ecef; font-weight: bold; }
        .total-label { text-align: right; padding-right: 10px; }
        .total-value { text-align: right; }

        /* --- No Data State --- */
        .no-data {
            text-align: center;
            padding: 2.5rem 1.25rem;
            color: var(--secondary-color);
            border: 2px dashed var(--medium-gray);
            border-radius: var(--border-radius);
            background-color: var(--light-gray);
        }
        .no-data svg { margin-bottom: 0.75rem; color: #adb5bd; width: 48px; height: 48px; }
        .no-data p { font-size: 1.1em; margin-bottom: 0.3rem; color: var(--dark-gray); font-weight: 500; }
        .no-data span { font-size: 0.9em; }

    </style>
</head>
<body>

<div class="page-wrapper">

    <header class="main-header">
        <div class="logo">Employee Payroll</div>
        <div class="header-right">
            <form method="POST" class="logout-form" onsubmit="return confirm('Are you sure you want to log out?');">
                <button type="submit" name="logout" class="button button-logout" title="Log Out">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd" />
                      <path fill-rule="evenodd" d="M16.78 9.72a.75.75 0 011.06 0l.75.75a.75.75 0 010 1.06l-.75.75a.75.75 0 11-1.06-1.06l.22-.22H11a.75.75 0 010-1.5h5.94l-.22-.22a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </header>

    <main class="content-area">
        <section class="form-section card">
            <h2 class="section-title">Add Employee</h2>

            <!-- Display Messages -->
            <?php if (!empty($message)): ?>
                <div class="message message-<?php echo htmlspecialchars($message_type); ?>">
                    <?php echo $message; // Allow HTML (like <ul>) from error messages ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="employee-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required placeholder="e.g., Junio">
                    </div>

                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" required placeholder="e.g., Annielyn">
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <select id="position" name="position" required>
                            <option value="" disabled selected>Select position...</option>
                            <?php foreach ($rateTable as $pos => $rate): ?>
                                <option value="<?php echo htmlspecialchars($pos); ?>"><?php echo htmlspecialchars($pos); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hrsWork">Hours Worked</label>
                        <input type="number" id="hrsWork" name="hrsWork" min="0.1" step="any" required placeholder="e.g., 100">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="addEmployee" class="button button-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        <span>Add Employee</span>
                    </button>
                    <button type="submit" name="reset" class="button button-secondary" onclick="return confirm('Are you sure you want to reset all payroll data? This action cannot be undone.');">
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201-4.42 5.5 5.5 0 011.619-3.11 1.5 1.5 0 012.472 1.113 3.5 3.5 0 006.53 2.017 1.5 1.5 0 012.472-1.113 5.5 5.5 0 01-3.89 5.514zM4.688 8.576a5.5 5.5 0 019.201 4.42 5.5 5.5 0 01-1.619 3.11 1.5 1.5 0 11-2.472-1.113 3.5 3.5 0 00-6.53-2.017 1.5 1.5 0 11-2.472 1.113 5.5 5.5 0 013.89-5.514z" clip-rule="evenodd" />
                         </svg>
                         <span>Reset Table</span>
                    </button>
                </div>
            </form>
        </section>

        <section class="payroll-section card">
            <h2 class="section-title">Payroll Summary</h2>
            <?php if (!empty($payrollData)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Position</th>
                                <th class="text-right">Rate</th>
                                <th class="text-center">Hrs Work</th>
                                <th class="text-right">Gross</th>
                                <th class="text-right">Bonus</th>
                                <th class="text-right">SSS</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right">PagIbig</th>
                                <th class="text-right">Total Deduction</th>
                                <th class="text-right net-pay-header">Netpay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payrollData as $data): ?>
                                <tr>
                                    <td><?php echo $data['SN']; ?></td>
                                    <td><?php echo displayValue($data['Last Name']); ?></td>
                                    <td><?php echo displayValue($data['First Name']); ?></td>
                                    <td><?php echo displayValue($data['Position']); ?></td>
                                    <td class="text-right"><?php echo displayValue($data['Rate']); ?></td>
                                    <td class="text-center"><?php echo $data['Hrs Work']; // Display hours as plain number ?></td>
                                    <td class="text-right"><?php echo displayValue($data['Gross']); ?></td>
                                    <td class="text-right"><?php echo displayValue($data['Bonus']); // Handles '-' ?></td>
                                    <td class="text-right"><?php echo displayValue($data['SSS']); ?></td>
                                    <td class="text-right"><?php echo displayValue($data['Tax']); // Handles '-' ?></td>
                                    <td class="text-right"><?php echo displayValue($data['PagIbig']); ?></td>
                                    <td class="text-right"><?php echo displayValue($data['Total Deduction']); ?></td>
                                    <td class="text-right net-pay"><?php echo displayValue($data['Netpay']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                         <tfoot>
                            <?php
                            // --- Calculate and Display Totals ---
                            if (count($payrollData) > 0) {
                                $totalGross = array_sum(array_column($payrollData, 'Gross'));
                                // Filter out '-' before summing Bonus and Tax
                                $totalBonus = array_sum(array_filter(array_column($payrollData, 'Bonus'), 'is_numeric'));
                                $totalSSS = array_sum(array_column($payrollData, 'SSS'));
                                $totalTax = array_sum(array_filter(array_column($payrollData, 'Tax'), 'is_numeric'));
                                $totalPagibig = array_sum(array_column($payrollData, 'PagIbig'));
                                $totalDeductions = array_sum(array_column($payrollData, 'Total Deduction'));
                                $totalNetpay = array_sum(array_column($payrollData, 'Netpay'));
                            ?>
                            <tr>
                                <td colspan="6" class="total-label">Totals:</td>
                                <td class="text-right total-value"><?php echo formatCurrency($totalGross); ?></td>
                                <td class="text-right total-value"><?php echo formatCurrency($totalBonus); ?></td>
                                <td class="text-right total-value"><?php echo formatCurrency($totalSSS); ?></td>
                                <td class="text-right total-value"><?php echo formatCurrency($totalTax); ?></td>
                                <td class="text-right total-value"><?php echo formatCurrency($totalPagibig); ?></td>
                                <td class="text-right total-value"><?php echo formatCurrency($totalDeductions); ?></td>
                                <td class="text-right total-value net-pay"><?php echo formatCurrency($totalNetpay); ?></td>
                            </tr>
                            <?php } ?>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>No employee data to display.</p>
                    <span>Add employees using the form above to see the payroll summary.</span>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="main-footer">
        © <?php echo date("Y"); ?> Payroll Calculator. All rights reserved.
    </footer>

</div> <!-- End page-wrapper -->

</body>
</html>