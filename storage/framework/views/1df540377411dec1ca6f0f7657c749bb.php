<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #FF6B35;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #FF6B35;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .section {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Report</h1>
        <p><?php echo e($tenant->name ?? 'TMS SaaS'); ?></p>
        <p>Generated on: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
        <?php if($filters['date_from'] || $filters['date_to']): ?>
            <p>Period: <?php echo e($filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'N/A'); ?> 
               to <?php echo e($filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'N/A'); ?></p>
        <?php endif; ?>
    </div>

    <?php if($filters['type'] !== 'expenses'): ?>
    <div class="section">
        <h2>Invoices (Revenue)</h2>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($invoice->invoice_number); ?></td>
                        <td><?php echo e($invoice->client->name ?? 'N/A'); ?></td>
                        <td>R$ <?php echo e(number_format($invoice->total_amount, 2, ',', '.')); ?></td>
                        <td><?php echo e(ucfirst($invoice->status)); ?></td>
                        <td><?php echo e($invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A'); ?></td>
                        <td><?php echo e($invoice->created_at->format('d/m/Y H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No invoices found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if($filters['type'] !== 'revenue'): ?>
    <div class="section">
        <h2>Expenses</h2>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($expense->description); ?></td>
                        <td>R$ <?php echo e(number_format($expense->amount, 2, ',', '.')); ?></td>
                        <td><?php echo e($expense->category ?? 'N/A'); ?></td>
                        <td><?php echo e(ucfirst($expense->status)); ?></td>
                        <td><?php echo e($expense->due_date ? $expense->due_date->format('d/m/Y') : 'N/A'); ?></td>
                        <td><?php echo e($expense->created_at->format('d/m/Y H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No expenses found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="summary">
        <h3>Summary</h3>
        <?php if($filters['type'] !== 'expenses'): ?>
            <p><strong>Total Revenue:</strong> R$ <?php echo e(number_format($invoices->sum('total_amount'), 2, ',', '.')); ?></p>
            <p><strong>Paid Revenue:</strong> R$ <?php echo e(number_format($invoices->where('status', 'paid')->sum('total_amount'), 2, ',', '.')); ?></p>
        <?php endif; ?>
        <?php if($filters['type'] !== 'revenue'): ?>
            <p><strong>Total Expenses:</strong> R$ <?php echo e(number_format($expenses->sum('amount'), 2, ',', '.')); ?></p>
            <p><strong>Paid Expenses:</strong> R$ <?php echo e(number_format($expenses->where('status', 'paid')->sum('amount'), 2, ',', '.')); ?></p>
        <?php endif; ?>
        <?php if($filters['type'] === 'all'): ?>
            <p><strong>Net Balance:</strong> R$ <?php echo e(number_format($invoices->sum('total_amount') - $expenses->sum('amount'), 2, ',', '.')); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>

















<?php /**PATH /var/www/resources/views/reports/financial-pdf.blade.php ENDPATH**/ ?>