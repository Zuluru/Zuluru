<tr>
	<td class="handle"></td>
	<td><?php
	echo $this->Form->hidden("Answer.$i.id", array('value' => $answer['id']));
	echo $this->Form->hidden("Answer.$i.sort", array('value' => $i));
	echo $this->Form->input("Answer.$i.answer", array(
			'div' => false,
			'label' => false,
			'type' => 'text',
			'size' => 60,
			'value' => $answer['answer'],
	));
	?></td>
	<td class="actions"><?php
		echo $this->Html->link (__('Delete', true), '#');
		$id = 'span_' . mt_rand(); ?>
		<span id="<?php echo $id; ?>">
		<?php
		if ($answer['active']) {
			echo $this->Js->link(__('Deactivate', true),
					array('controller' => 'answers', 'action' => 'deactivate', 'answer' => $answer['id'], 'id' => $id),
					array('update' => "#temp_update")
			);
		} else {
			echo $this->Js->link(__('Activate', true),
					array('controller' => 'answers', 'action' => 'activate', 'answer' => $answer['id'], 'id' => $id),
					array('update' => "#temp_update")
			);
		}
		?>
		</span>
	</td>
</tr>
