<?php

if (!isset($rowData)) {
    return;
}
?>

    <tr id="system-row-<?php echo $rowData['id']; ?>">
    <td>
        <?php echo fa_number($rownum); ?>
    </td>


    <td class="td-computer-code">
        <strong><?php echo htmlspecialchars($rowData['computer_code'] ?? '-'); ?></strong>
        <?php if ($rowData['property_code']): ?>
            <br><small class="text-muted">اموال: <?php echo htmlspecialchars($rowData['property_code']); ?></small>
        <?php endif; ?>
    </td>

    <!-- نام سیستم -->
    <td class="td-name">
        <?php echo htmlspecialchars($rowData['name'] ?? '-'); ?></td>

    <!-- بخش -->
    <td class="td-department">
        <?php echo htmlspecialchars($rowData['department_name'] ?? '-'); ?></td>

    <!-- CPU -->
    <td class="td-cpu">
        <?php if ($rowData['cpu_brand'] && $rowData['cpu_model']): ?>
            <strong><?php echo htmlspecialchars($rowData['cpu_brand']); ?></strong>
            <br><small><?php echo htmlspecialchars($rowData['cpu_model']); ?></small>
        <?php else: ?>
            <span class="badge badge-secondary">-</span>
        <?php endif; ?>
    </td>

    <!-- مادربرد -->
    <td class="td-motherboard">
        <?php if ($rowData['motherboard_brand'] && $rowData['motherboard_model']): ?>
            <strong><?php echo htmlspecialchars($rowData['motherboard_brand']); ?></strong>
            <br><small><?php echo htmlspecialchars($rowData['motherboard_model']); ?></small>
        <?php else: ?>
            <span class="badge badge-secondary">-</span>
        <?php endif; ?>
    </td>
        <!-- پاور -->
        <td class="td-power">
            <?php if ($rowData['power_brand'] && $rowData['power_model']): ?>
                <strong><?php echo htmlspecialchars($rowData['power_brand']); ?></strong>
                <br><small><?php echo htmlspecialchars($rowData['power_model']); ?></small>
            <?php else: ?>
                <span class="badge badge-secondary">-</span>
            <?php endif; ?>
        </td>

        <!-- مانیتور -->
        <td class="td-monitor">
            <?php if ($rowData['monitor_brand'] && $rowData['monitor_model']): ?>
                <strong><?php echo htmlspecialchars($rowData['monitor_brand']); ?></strong>
                <br><small><?php echo htmlspecialchars($rowData['monitor_model']); ?></small>
                <?php if ($rowData['monitor_property_code']): ?>
                    <br><small
                            class="text-muted">اموال: <?php echo htmlspecialchars($rowData['monitor_property_code']); ?></small>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge badge-secondary">-</span>
            <?php endif; ?>
        </td>

    <!-- رم -->
    <td class="td-rams">
        <?php if (!empty($rowData['rams'])): ?>
            <?php foreach ($rowData['rams'] as $ram): ?>
                <div class="item-small">
                    <?php echo htmlspecialchars($ram['brand_name'] ?? ''); ?>
                    <small><?php echo htmlspecialchars($ram['model_name'] ?? ''); ?></small>
                    <?php if ($ram['capacity']): ?>
                        <small>(<?php echo htmlspecialchars($ram['capacity']); ?>)</small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="badge badge-secondary">-</span>
        <?php endif; ?>
    </td>

    <!-- هارد -->
    <td class="td-storages">
        <?php if (!empty($rowData['storages'])): ?>
            <?php foreach ($rowData['storages'] as $storage): ?>
                <div class="item-small">
                    <?php echo htmlspecialchars($storage['brand_name'] ?? ''); ?>
                    <small><?php echo htmlspecialchars($storage['model_name'] ?? ''); ?></small>
                    <?php if ($storage['capacity']): ?>
                        <small>(<?php echo htmlspecialchars($storage['capacity']); ?>)</small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="badge badge-secondary">-</span>
        <?php endif; ?>
    </td>


    <!-- IP -->
    <td class="td-ips">
        <?php if (!empty($rowData['ips'])): ?>
            <?php foreach ($rowData['ips'] as $ip): ?>
                <div class="item-small">
                    <span class="ip-address"><?php echo htmlspecialchars($ip['ip_address']); ?></span>
                    <br><small class="text-muted"><?php echo htmlspecialchars($ip['network_type']); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="badge badge-secondary">-</span>
        <?php endif; ?>
    </td>

    <!-- تجهیزات جانبی -->
    <td class="td-peripherals">
        <?php if (!empty($rowData['peripherals'])): ?>

            <?php
            $grouped = [];

            foreach ($rowData['peripherals'] as $periph) {
                $grouped[$periph['type_name']][] = $periph;
            }
            foreach ($grouped as $type => $items): ?>
                <div class="peripheral-group">
                    <strong><?= htmlspecialchars($type) ?></strong>

                    <?php foreach ($items as $item): ?>
                        <div class="peripheral-item">
                            <?= htmlspecialchars($item['brand_name']) ?>
                            <?= htmlspecialchars($item['model_name']) ?>

                            <?php if (!empty($item['property_code'])): ?>
                                (<?= htmlspecialchars($item['property_code']) ?>)
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="badge badge-secondary">-</span>
        <?php endif; ?>
    </td>

    <!-- تاریخ -->
    <td class="td-date date">
        <?php echo fa_number(htmlspecialchars($rowData['created_at'] ?? '-')); ?>
        <br><small><?php echo htmlspecialchars($rowData['creator_name'] ?? '-'); ?></small>
    </td>

    <!-- عملیات -->
    <td class="td-actions action-buttons">
        <?php if (canEditSystems()): ?>
            <button class="edit-btn" onclick='openEditModal(<?php echo json_encode($rowData); ?>)' title="ویرایش">
                ✏️ویرایش
            </button>
        <?php endif; ?>
        <?php if (canDeleteSystems()): ?>
            <button class="delete-btn"
                    onclick="confirmDelete(<?php echo $rowData['id']; ?>, '<?php echo htmlspecialchars($rowData['name']); ?>')"
                    title="حذف">🗑️حذف
            </button>
        <?php endif; ?>
    </td>
    </tr>