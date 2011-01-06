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
		echo $this->Html->link ('Delete', '#');
	?></td>
</tr>
