<?php
$fp = fopen('php://output','w+');
fputcsv($fp, array(
		__('Category', true),
		__('Task', true),
		__('Reporting To', true),
		__('Date', true),
		__('Start', true),
		__('End', true),
		__('Assignee', true),
		__('Approved By', true),
		__('Email', true),
		__('Home Phone', true),
		__('Work Phone', true),
		__('Work Ext', true),
		__('Mobile Phone', true),
));

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
						$slot['Person']['home_phone'],
						$slot['Person']['work_phone'],
						$slot['Person']['work_ext'],
						$slot['Person']['mobile_phone'],
				));
			}
			fputcsv($fp, $row);
		}
	}
}

fclose($fp);
?>
