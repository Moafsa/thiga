<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CT-es Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>CT-es Report</h1>
        <p><?php echo e($tenant->name ?? 'TMS SaaS'); ?></p>
        <p>Generated on: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
        <?php if($filters['date_from'] || $filters['date_to']): ?>
            <p>Period: <?php echo e($filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'N/A'); ?> 
               to <?php echo e($filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'N/A'); ?></p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Access Key</th>
                <th>Status</th>
                <th>Sender Client</th>
                <th>Receiver Client</th>
                <th>Tracking Number</th>
                <th>Created At</th>
                <th>Authorized At</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $ctes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($cte->mitt_number ?? 'N/A'); ?></td>
                    <td style="font-family: monospace; font-size: 10px;"><?php echo e($cte->access_key ?? 'N/A'); ?></td>
                    <td><?php echo e($cte->status_label); ?></td>
                    <td><?php echo e($cte->shipment->senderClient->name ?? 'N/A'); ?></td>
                    <td><?php echo e($cte->shipment->receiverClient->name ?? 'N/A'); ?></td>
                    <td><?php echo e($cte->shipment->tracking_number ?? 'N/A'); ?></td>
                    <td><?php echo e($cte->created_at->format('d/m/Y H:i')); ?></td>
                    <td><?php echo e($cte->authorized_at ? $cte->authorized_at->format('d/m/Y H:i') : 'N/A'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No CT-es found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary">
        <strong>Summary:</strong> Total CT-es: <?php echo e($ctes->count()); ?> | 
        Authorized: <?php echo e($ctes->where('status', 'authorized')->count()); ?> | 
        Pending: <?php echo e($ctes->where('status', 'pending')->count()); ?> | 
        Rejected: <?php echo e($ctes->where('status', 'rejected')->count()); ?>

    </div>
</body>
</html>

<?php /**PATH /var/www/resources/views/fiscal/reports/ctes-pdf.blade.php ENDPATH**/ ?>