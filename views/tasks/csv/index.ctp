<?php
$fp = fopen('php://output','w+');
$headers = array(
		__('Category', true),
		__('Task', true),
		__('Reporting To', true),
		__('Date', true),
		__('Start', true),
		__('End', true),
		__('Assignee', true),
		__('Approved By', true),
		__('Email', true),
);
if (Configure::read('profile.home_phone')) {
	$headers[] = __('Home Phone', true);
}
if (Configure::read('profile.work_phone')) {
	$headers[] = __('Work Phone', true);
	$headers[] = __('Work Ext', true);
}
if (Configure::read('profile.mobile_phone')) {
	$headers[] = __('Mobile Phone', true);
}

fputcsv($fp, $headers);

foreach ($tasks as $category) {
	foreach ($category['Task'] as $task) {
		foreach ($task['TaskSlot'] as $slot) {
			$row = array(
					$category['Category']['name'],
					$task['name'],
					$task['Person']['full_name'],
					$slot['task_date'],
					$slot['task_start'],
					$slot['task_end'],
			);
			if (!empty($slot['Person'])) {
				$row = array_merge($row, array(
						$slot['Person']['full_name'],
						$slot['approved'] ? $slot['ApprovedBy']['full_name'] : '',
						$slot['Person']['email'],
				));
				if (Configure::read('profile.home_phone')) {
					$row[] = $slot['Person']['home_phone'];
				}
				if (Configure::read('profile.work_phone')) {
					$row[] = $slot['Person']['work_phone'];
					$row[] = $slot['Person']['work_ext'];
				}
				if (Configure::read('profile.mobile_phone')) {
					$row[] = $slot['Person']['mobile_phone'];
				}
			}
			fputcsv($fp, $row);
		}
	}
}

fclose($fp);
?>
