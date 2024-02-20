<?php

class mptt extends Common {
	/**
	 * MPTT (Modified Pre-ordered Tree Traversal) Class.
	 * Only for tree maintence (no methods for printing the tree and such).
	 * Class selects needed data from the database.
	 *
	 * Make sure you have set the constants:
	 *  $this->table - The name of the table in which the tree data is stored.
	 *  $this->pkey - The name of the PRIMARY KEY column (the one with row id's).
	 *  $this->left_id - The name of the column in which the left id's are stored.
	 *  $this->right_id - The name of the column in which the right id's are stored.
	 *  $this->level - The name of the column in which the elemts levels are stored.
	 *
	 * All 4 columns are absolutely REQUIRED for the class to function.
	 *
	 * @author marek_mar
	 *
	 * @version Second Release
	 *
	 * @todo Find some more bugs...
	 */

	/**
	 * @var MySQL resource
	 */

	// Normal constants used as there are no class constants in PHP4.
	var $link;
	
	// Public methods

	/**
	 * Constructor.
	 *
	 * @param (optional) MySQL resource $link
	 * @return bool(true)
	 */
	
	function __construct($table, $l_id='left_id', $r_id='right_id', $level='level', $key='id', $title='title', $link = NULL) {
		parent::__construct();
		$this->table = $table;
		$this->left_id = $l_id;
		$this->right_id = $r_id;
		$this->level = $level;
		$this->pkey = $key;
		$this->title = $title;
		$this->link = $link;
		return true;
	}

	/**
	 * Add an element to the tree as a child of $parent and as $child_num'th child. If $data is not supplied the insert id will be returned.
	 *
	 * @param int $parent
	 * @param int $child_num
	 * @param array $misc_data
	 * @return bool or int
	 */
	function add($parent, $child_num = 0, $misc_data = false) {
		$this->logMessage('add', sprintf('(%d, %d, [%s])', $parent, $child_num, print_r($misc_data,true)), 2);
	    if ($parent == "" || $parent == null) $parent = 0;
		if(!is_numeric($parent) || $parent < 0)
		{
		    $this->logMessage("add","bad data parent [$parent]",1);
			return false;
		}
		if($parent != 0)
		{
			$sql = 'SELECT `' . $this->left_id . '` AS `'.$this->left_id.'`, `' . $this->right_id . '` AS `'.$this->right_id.'`, `' . $this->level . '` AS `level` FROM `' . $this->table . '` WHERE `' . $this->pkey . '` = ' . $parent . ';';
			if (!($parent = $this->fetchSingle($sql)))
				return false;
		}
		else
		{
			// Virtual root element as parent.
			$parent = $this->get_virtual_root();
		}
		$children = $this->get_children($parent[$this->left_id], $parent[$this->right_id], $parent['level']);

		if(count($children) == 0)
		{
			$child_num = 0;
		}

		//We have what we want.
		$sql = array();
		
		if($child_num == 0)	// || (count($children) - $child_num) <= 0 || (count($children) + $child_num + 1) < 0)
		{
			$boundry = array($this->left_id, $this->right_id, $parent[$this->left_id]);
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = `' . $this->left_id . '` + 2 WHERE `' . $boundry[0] . '` > ' . $boundry[2] . ' AND `' . $boundry[1] . '` > ' . $boundry[2] . ';';
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->right_id . '` = `' . $this->right_id . '` + 2 WHERE `' . $boundry[1] . '` > ' . $boundry[2] . ';';
		}
		elseif($child_num != 0)
		{
			// Some other child.
			if($child_num < 0)
			{
				$child_num = max(0,count($children) + $child_num);	// + 1;
			}
			if($child_num > count($children))
			{
				$child_num = count($children);
			}
			$boundry = array($this->right_id, $this->left_id, $children[$child_num - 1][$this->right_id]);
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = `' . $this->left_id . '` + 2 WHERE `' . $boundry[0] . '` > ' . $boundry[2] . ' AND `' . $boundry[1] . '` > ' . $boundry[2] . ';';
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->right_id . '` = `' . $this->right_id . '` + 2 WHERE `' . $boundry[0] . '` > ' . $boundry[2] . ';';
		}
		else
		{
			return false;
		}


		// Make a hole for the new element.
		//$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = `' . $this->left_id . '` + 2 WHERE `' . $boundry[0] . '` > ' . $boundry[2] . ' AND `' . $boundry[1] . '` > ' . $boundry[2] . ';';
		//$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->right_id . '` = `' . $this->right_id . '` + 2 WHERE `' . $boundry[1] . '` > ' . $boundry[2] . ';';
		// Insert the new element.

		$data = array(
			$this->left_id => $boundry[2] + 1,
			$this->right_id => $boundry[2] + 2,
			$this->level => $parent['level'] + 1
		);
		if($misc_data && is_array($misc_data))
		{
			$data = array_merge($misc_data, $data);
			
		} 
		$data = $this->build_sql($data);
		$sql[] = 'INSERT INTO `' . $this->table . '` SET ' . $data . ';';
		//$insert = 'INSERT INTO `' . $this->table . '` SET ' . $data . ';';
		$this->logMessage("add",sprintf("insert sql [%s]",print_r($sql,true)),3);

		// Now we have to run the SQL.
		//$this->beginTransaction();
		$ret = $this->exec_sql($sql);
		$id =  $this->insertId();	//mysql_insert_id();
		$this->logMessage("add","insert id [$id]",3);
		$errors = array();
		$this->check_consistency($errors);
		if (count($errors) > 0 || !$ret) {
			$this->logMessage("add","errors found [".print_r($errors)."]",1,true);
			//$this->rollbackTransaction();
			$id = -1;
		}
		else {
			//$this->commitTransaction();
		}
		return $id;
	}

	/**
	 * Deletes element $id with or without children. If children should be kept they will become children of $id's parent.
	 *
	 * @param int $id
	 * @param bool $keep_children
	 * @return bool
	 */
	function delete($id, $keep_children = false) {
		$this->logMessage('delete', sprintf('(%d, %d)', $id, $keep_children), 2);
		if(!is_numeric($id) || $id <= 0 || !is_bool($keep_children))
		{
			return false;
		}
		$sql = 'SELECT `' . $this->left_id . '` AS `'.$this->left_id.'`, `' . $this->right_id . '` AS `'.$this->right_id.'`, `' . $this->level . '` AS `level` FROM `' . $this->table . '` WHERE `' . $this->pkey . '` = ' . $id . ';';
		if (!($a = $this->fetchSingle($sql))) {
			$this->logMessage('delete', sprintf('missing record [%d$] sql [%s]',$id,$sql), 1, true);
			return false;
		}
		$sql = array();
		if(!$keep_children)
		{
			// Delete the element with children.
			$sql[] = 'DELETE FROM `' . $this->table . '` WHERE `' . $this->left_id . '` >= ' . $a[$this->left_id] . ' AND `' . $this->right_id . '` <= ' . $a[$this->right_id] . ';';
			// Remove the hole.
			$diff = $a[$this->right_id] - $a[$this->left_id] + 1;
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = `' . $this->left_id . '` - ' . $diff . ' WHERE `' . $this->right_id . '` > ' . $a[$this->right_id] . ' AND `' . $this->left_id . '` > ' . $a[$this->right_id] . ';';
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->right_id . '` = `' . $this->right_id . '` - ' . $diff . ' WHERE `' . $this->right_id . '` > ' . $a[$this->right_id] . ';';
			// No level cahnges needed.
		}
		else
		{
			// Delete ONLY the element.
			$sql[] = 'DELETE FROM `' . $this->table . '` WHERE `' . $this->pkey . '` = ' . $id . ';';
			// Fix children.
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = `' . $this->left_id . '` - 1, `' . $this->right_id . '` = `' . $this->right_id . '` - 1, `' . $this->level . '` = `' . $this->level . '` - 1 WHERE `' . $this->left_id . '` >= ' . $a[$this->left_id] . ' AND `' . $this->right_id . '` <= ' . $a[$this->right_id] . ';';
			// Remove hole.
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = `' . $this->left_id . '` - 2 WHERE `' . $this->right_id . '` > ' . ($a[$this->right_id] - 1) . ' AND `' . $this->left_id . '` > ' . ($a[$this->right_id] - 1) . ';';
			$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->right_id . '` = `' . $this->right_id . '` - 2 WHERE `' . $this->right_id . '` > ' . ($a[$this->right_id] - 1) . ';';
		}

		// Run SQL.
		$this->beginTransaction();
		$ret = $this->exec_sql($sql);
		$errors = array();
		$this->check_consistency($errors);
		if (count($errors) > 0 || !$ret) {
			$this->rollbackTransaction();
			$ret = false;
		}
		else {
			$this->commitTransaction();
		}
		return $ret;
	}

	
	function move($id, $target_id, $child_num = 0) {
		$this->logMessage('move', sprintf('(%d, %d, %d)', $id, $target_id, $child_num), 2);
		
		if(!is_numeric($id) || !is_numeric($target_id) || !is_numeric($child_num))
		{
			return false;
		}
		if($target_id != 0)
		{
			$sql = 'SELECT ' . $this->left_id . ' AS '.$this->left_id.', ' . $this->right_id . ' AS right_id, ' . $this->level . ' AS level
						FROM ' . $this->table . '
						WHERE ' . $this->pkey . ' = ' . $id . '
							OR ' . $this->pkey . ' = ' . $target_id . '
						ORDER BY ' . $this->pkey . ' ' . (($id < $target_id) ? 'ASC' : 'DESC') . ';';

			if (!($recs = $this->fetchAll($sql))) {
				$this->logMessage('move', sprintf('invalid target id [%d] target [%d]',$id,$target_id),1,true);
				return false;
			}
			if (count($recs) != 2) return false;
			$a = $recs[0];
			$b = $recs[1];
		}
		else
		{
			$sql = 'SELECT ' . $this->left_id . ' AS '.$this->left_id.', ' . $this->right_id . ' AS right_id, ' . $this->level . ' AS level
						FROM ' . $this->table . '
						WHERE ' . $this->pkey . ' = ' . $id . ';';
			if (!($a = $this->fetchSingle($sql))) {
				$this->logMessage('move', sprintf('invalid id [%d]', $id), 1, true);
			}
			// Virtual root element.
			$b = $this->get_virtual_root();
		}

		// We need to get the children.
		$children = $this->get_children($b[$this->left_id], $b[$this->right_id], $b['level']);

		if(count($children) == 0) $child_num = 0;
		if($child_num > count($children)) $child_num = count($children)+1;
		//if($child_num == 0 || (count($children) - $child_num) <= 0 || (count($children) + $child_num + 1) < 0) {
		if($child_num == 0 || (count($children) - $child_num) < 0 || (count($children) + $child_num + 1) < 0) {
				// First child.
				$boundry = array($this->left_id, $this->right_id, $this->right_id, $b[$this->left_id]);
		}
		elseif($child_num != 0)
		{
			// Some other child.
			if($child_num < 0)
			{
				$child_num = count($children) + $child_num + 1;
			}
			if($child_num > count($children))
			{
				$child_num = count($children);
			}
			$boundry =  array($this->right_id, $this->left_id, $this->right_id, $children[$child_num - 1][$this->right_id]);
		}
		else
		{
			return false;
		}


		// Math.
		$diff = $a[$this->right_id] - $a[$this->left_id] + 1; // The "size" of the tree.

		if($a[$this->left_id] < $boundry[3])
		{
			$size = $boundry[3] - $diff;
			$dist = $boundry[3] - $diff - $a[$this->left_id] + 1;
		}
		else
		{
			$size = $boundry[3];
			$dist = $boundry[3] - $a[$this->left_id] + 1;
		}
		// Level math.
		$ldiff = ($a['level'] - $b['level'] - 1) * -1;
		// We have all what we need.


		$sql = array();

		// Give the needed rows negative id's.
		$sql[] = 'UPDATE ' . $this->table . '
					SET ' . $this->left_id . ' = ' . $this->left_id . ' * -1, ' . $this->right_id . ' = ' . $this->right_id . ' * -1
					WHERE ' . $this->left_id . ' >= ' . $a[$this->left_id] . '
						AND ' . $this->right_id . ' <= ' . $a[$this->right_id] . ';';
		// Remove the hole.
		$sql[] = 'UPDATE ' . $this->table . '
					SET ' . $this->left_id . ' = ' . $this->left_id . ' - ' . $diff . '
					WHERE ' . $this->right_id . ' > ' . $a[$this->right_id] . '
						AND ' . $this->left_id . ' > ' . $a[$this->right_id] . ';';
		$sql[] = 'UPDATE ' . $this->table . '
					SET ' . $this->right_id . ' = ' . $this->right_id . ' - ' . $diff . '
					WHERE ' . $this->right_id . ' > ' . $a[$this->right_id] . ';';
		// Add new hole.
		$sql[] = 'UPDATE ' . $this->table . '
					SET ' . $this->left_id . ' = ' . $this->left_id . ' + ' . $diff . '
					WHERE ' . $boundry[0] . ' > ' . $size . '
						AND ' . $boundry[1] . ' > ' . $size . ';';
		$sql[] = 'UPDATE ' . $this->table . '
					SET ' . $this->right_id . ' = ' . $this->right_id . ' + ' . $diff . '
						WHERE ' . $boundry[2] . ' > ' . $size . ';';
		// Fill hole & update rows & multiply by -1.
		$sql[] = 'UPDATE ' . $this->table . '
					SET ' . $this->left_id . ' = (' . $this->left_id . ' - (' . $dist . ')) * -1, ' . $this->right_id . ' = (' . $this->right_id . ' - (' . $dist . ')) * -1, ' . $this->level . ' = ' . $this->level . ' + (' . $ldiff . ')
					WHERE ' . $this->left_id . ' < 0;';


		// Now we need to execute the queries.
		$this->logMessage("move","sql [".print_r($sql,true)."]",3);
		//mysql_query("SET SESSION TRANSACTION READ UNCOMMITTED");
		$this->beginTransaction();
		$ret = $this->exec_sql($sql);
		$errors = array();
		$this->check_consistency($errors);
		$this->logMessage("move","ret [$ret], errors [".count($errors)."]",3);
		if (count($errors) > 0 || !$ret) {
			$this->rollbackTransaction();
			$this->check_consistency($errors);
			$ret = false;
		}
		else {
			$this->commitTransaction();
		}
		return $ret;
	}
	



	/**
	 * Copies element $id (with children) to $parent as the $child_mun'th child.
	 *
	 * @param int $id
	 * @param int $parent
	 * @param int $child_num
	 * @return bool
	 */
	function copy($id, $parent, $child_num = 0) {
		$this->logMessage('copy', sprintf('(%d, %d, [%s])', $id, $parent, $child_num), 2);
		if(!is_numeric($id) || $id < 0 ||!is_numeric($parent) || $parent < 0)
		{
			return false;
		}

		// Get branch left & right id's.
		$sql = 'SELECT `' . $this->left_id . '` AS `'.$this->left_id.'`, `' . $this->right_id . '` AS `'.$this->right_id.'`, `' . $this->level . '` AS `level` FROM `' . $this->table . '` WHERE `' . $this->pkey . '` = ' . $id . ';';
		if (!($a = $this->fetchSingle($sql))) {
			$this->logMessage('copy', sprintf('invalid copy source id [%d] parent [%d] sql [%s]', $id, $parent, $sql), 1, true);
			return false;
		}
		// Get child data.
		$sql = 'SELECT * FROM `' . $this->table . '` WHERE `' . $this->left_id . '` >= ' . $a[$this->left_id] . ' AND `' . $this->right_id . '` <= ' . $a[$this->right_id] . ';';
		$data = $this->fetchAll($sql);
		if($parent != 0)
		{
			$sql = 'SELECT `' . $this->left_id . '` AS `'.$this->left_id.'`, `' . $this->right_id . '` AS `'.$this->right_id.'`, `' . $this->level . '` AS `level` FROM `' . $this->table . '` WHERE `' . $this->pkey . '` = ' . $parent . ';';
			if (!$b = $this->fetchSingle($sql)) {
				$this->logMessage('copy', sprintf('invalid destination id [%d] parent [%d] sql [$sql]', $id, $parent, $sql), 1, true);
				return false;
			}
		}
		else
		{
			$b = $this->get_virtual_root();
		}

		// Get target's children.
		$children = $this->get_children($b[$this->left_id], $b[$this->right_id], $b['level']);
		
		if(count($children) == 0)
		{
			$child_num = 0;
		}
		if($child_num == 0 || (count($children) - $child_num) <= 0 || (count($children) + $child_num + 1) < 0)
		{
			// First child.
			$boundry = array($this->left_id, $this->right_id, $this->right_id, $b[$this->left_id]);
		}
		elseif($child_num != 0)
		{
			// Some other child.
			if($child_num < 0)
			{
				$child_num = count($children) + $child_num + 1;
			}
			if($child_num > count($children))
			{
				$child_num = count($children);
			}
			$boundry =  array($this->right_id, $this->left_id, $this->right_id, $children[$child_num - 1][$this->right_id]);
		}
		else
		{
			return false;
		}

		// Math.
		$diff = $a[$this->right_id] - $a[$this->left_id] + 1;
		$dist = $boundry[3] - $a[$this->left_id] + 1;
		// Level math.
		$ldiff = ($a['level'] - $b['level'] - 1);

		// We have all we need.
		$sql = array();

		// Add hole.
		$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = `' . $this->left_id . '` + ' . $diff . ' WHERE `' . $boundry[0] . '` > ' . $boundry[3] . ' AND `' . $boundry[1] . '` > ' . $boundry[3] . ';';
		$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->right_id . '` = `' . $this->right_id . '` + ' . $diff . ' WHERE `' . $boundry[2] . '` > ' . $boundry[3] . ';';

		// Now we have to insert all the new elements.
		for($i = 0, $n = count($data); $i< $n; $i++)
		{
			// We need a new key.
			unset($data[$i][$this->pkey]);

			// This fields need new values.
			$data[$i][$this->left_id] += $dist;
			$data[$i][$this->right_id] += $dist;
			$data[$i][$this->level] -= $ldiff;

			$data[$i] = $this->build_sql($data[$i]);
			$sql[] = 'INSERT INTO `' . $this->table . '` SET ' . $data[$i];
		}

		// Run SQL
		$this->beginTransaction();
		$ret = $this->exec_sql($sql);
		$errors = array();
		$this->check_consistency($errors);
		if (count($errors) > 0 || !$ret) {
			$this->rollbackTransaction();
		}
		else {
			$this->commitTransaction();
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Swaps two elements and ONLY the elements!
	 *
	 * @param int $id1
	 * @param int $id2
	 * @return bool
	 */
	function swap($id1, $id2) {
		$this->logMessage('swap', sprintf('(%d, %d)', $id1, $id2), 2);
		
		if(!is_numeric($id1) || $id1 <= 0 || !is_numeric($id2) || $id2 <= 0)
		{
			return false;
		}
		$sql = 'SELECT `' . $this->left_id . '` AS `'.$this->left_id.'`, `' . $this->right_id . '` AS `'.$this->right_id.'`, `' . $this->level . '` AS `level` FROM `' . $this->table . '` WHERE `' . $this->pkey . '` = ' . $id1 . ' OR `' . $this->pkey . '` = ' . $id2;
				// I want the to be returned in order.
				$sql .= ' ORDER BY `id` ' . (($id1 < $id2) ? 'ASC' : 'DESC') . ';';
		if (!($recs = $this->fetchAll($sql))) {
			$this->logMessage('swap', sprintf('invalid source [%d] destination [%d] sql [%s]', $id1, $id2, $sql), 1, true);
			return false;
		}
		if (count($recs) != 2) {
			$this->logMessage('sawp', sprintf('source [%d] or destination [%d] missing, sql [%s]', $id1, $id2, $sql), 1, true);
			return false;
		}
		$a = $recs[0];
		$b = $recs[1];

		$sql = array();
		// Swap a with b.
		$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = ' . $b[$this->left_id] . ', `' . $this->right_id . '` = ' . $b[$this->right_id] . ', `' . $this->level . '` = ' . $b['level'] . ' WHERE `' . $this->pkey . '` = ' . $id1 . ';';
		// Swap b with a.
		$sql[] = 'UPDATE `' . $this->table . '` SET `' . $this->left_id . '` = ' . $a[$this->left_id] . ', `' . $this->right_id . '` = ' . $a[$this->right_id] . ', `' . $this->level . '` = ' . $a['level'] . ' WHERE `' . $this->pkey . '` = ' . $id2 . ';';

		// Now we need to execute the queries.
		$this->beginTransaction();
		$ret = $this->exec_sql($sql);
		$errors = array();
		$this->check_consistency($errors);
		if (count($errors) > 0 || !$ret) {
			$this->rollbackTransaction();
		}
		else {
			$this->commitTransaction();
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Check if all of the tree's left and right id's are correct.
	 *
	 * @param array $errors
	 * @return bool
	 */
	function check_consistency(&$errors) {
		$sql = 'SELECT * FROM `' . $this->table . '` ORDER BY `' . $this->left_id . '`;';
		if (!($recs = $this->fetchAll($sql))) {
			$this->logMessage('check_consistency', sprintf('main select failed sql [$sql]',$sql), 1,true);
			return false;
		}

		$data = array();
		$ids = array();

		foreach($recs as $row) {
			$this->logMessage("check_consistency","row [".$row["id"]."], left [".$row[$this->left_id]."] right [".$row["right_id"]."]",3);
			$data[] = $row;
			$ids[] = $row[$this->left_id];
			$ids[] = $row[$this->right_id];
		}

		rsort($ids, SORT_NUMERIC);
		$element_count = $ids[0] / 2;
		if(is_float($element_count))
		{ // If your first element has left_id=1 the last_right id must be even.
			$errors[] = 'Last right id is odd.';
			$element_count = ceil($element_count);
		}
		if(count($data) != ($ids[0] / 2))
		{ // It should be equal.
			$errors[] = 'Missing elements.';
			// Let's find the missing ones.
		}

		for($i = 0, $n = count($data); $i < $n; $i++)
		{
			// Elemnts left id & right id should never be both even or both odd.
			// This is from observation. If you have something against it I'll be happy to know.
			if(!(($data[$i][$this->left_id] % 2) xor ($data[$i][$this->right_id] % 2)))
			{
				$errors[] = 'Element with the id: ' . $data[$i][$this->pkey] . ' has invalid left/right id\'s.';
			}
			// The left id should always be smaller than the right id.
			if($data[$i][$this->left_id] > $data[$i][$this->right_id])
			{
				$errors[] = 'Element with the id: ' . $data[$i][$this->pkey] . ' has has too big left id.';
			}
		}

		// Elements might not be missing and have correct left&right id values but they might be off anyway (Two elements with the same id's for example).
		$id_sum = (1 + $ids[0]) * count($data);
		if(array_sum($ids) != $id_sum)
		{
			$errors[] = 'The sum of all left and right id\'s doesn\'t match the sum of left and right id\'s of the elements found.';
		}

		if(count($errors) > 0)
		{ // Let's check which left id's are missing.
			sort($ids, SORT_NUMERIC);
			$this->logMessage("check_consistency","sorted ids [".print_r($ids,true)."]",1,true);
			for($i = 1, $n = count($ids); $i < $n; $i++) {
				if($i != $ids[$i - 1])
				{
					$errors[] = 'Missing left/right id: ' . $i;
				}
			}
			// Tree is not consistant.
			return false;
		}
		// Tree is consistant.
		return true;
	}


	// Private methods
	// Errors coused by using these methods by yourself are not considered errors or bugs.

	/**
	 * Should be a private method. Do not use.
	 *
	 * @param int $left_id
	 * @param int $right_id
	 * @param int $level
	 * @return array
	 */
	function get_children($left_id, $right_id, $level) {
		$this->logMessage('get_children', sprintf('(%d, %d, %d)', $left_id, $right_id, $level), 2);
		
		$sql = 'SELECT *
					FROM ' . $this->table . '
					WHERE ' . $this->left_id . ' > ' . $left_id . '
						AND ' . $this->right_id . ' < ' . $right_id . '
						AND ' . $this->level . ' = ' . ($level + 1) . ' ORDER BY '.$this->left_id.' ASC;';

		$children = $this->fetchAll($sql);
		return $children;
	}

	/**
	 * Return the left_id, right_id and level for the virtual root node.
	 *
	 * @return array
	 */
	function get_virtual_root()
	{
			// Virtual root element as parent.
			//$sql = 'SELECT `' . $this->right_id . '` AS `'.$this->right_id.'` FROM `' . $this->table . '` ORDER BY `' . $this->right_id . '` DESC LIMIT 1;';
			
			$sql = 'SELECT MAX(' . $this->right_id . ') AS right_id
						FROM ' . $this->table . ';';
			$rec = $this->fetchScalar($sql);
			$root = array($this->left_id => 0, $this->right_id => $rec + 1, 'level' => 0);
			return $root;
	}

	/**
	 * Executes multiple query.
	 *
	 * @param array $sql
	 */
	function exec_sql($sql)
	{
		for($i = 0, $n = count($sql); $i < $n; $i++)
		{
			if (!$this->execute($sql[$i]))
				return false;
		}
		return true;
	}

	/**
	 * Build INSERT statement
	 *
	 * @param array $data
	 * @return array
	 */
	function build_sql($data)
	{
		foreach($data as $k => $v)
		{
			if(is_numeric($v))
			{
				$data[$k] = '`' . $k . '` = ' . $v . '';
			}
			else
			{
				$data[$k] = '`' . $k . '` = \'' . mysqli_real_escape_string($GLOBALS['globals']->getConnection(),$v) . '\'';
			}
		}
		return implode(', ', $data);
	}

	function fetchChildren($id) {
		$data = $this->fetchSingle(sprintf('select * from %s where %s = %d', $this->table, $this->pkey, $id));
		$sql = sprintf('select * from %s where %s = %d and %s >= %d and %s <= %d', $this->table, $this->level, $data[$this->level]+1, $this->left_id, $data[$this->left_id], $this->right_id, $data[$this->right_id]);
		$children = $this->fetchAll($sql);
		return $children;
	}
}
?>