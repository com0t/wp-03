<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for creating HTML Grids
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       1.5
 */
abstract class JHtmlGrid
{
	/**
	 * Display a boolean setting widget.
	 *
	 * @param   integer  $i        The row index.
	 * @param   integer  $value    The value of the boolean field.
	 * @param   string   $taskOn   Task to turn the boolean setting on.
	 * @param   string   $taskOff  Task to turn the boolean setting off.
	 *
	 * @return  string   The boolean setting widget.
	 *
	 * @since   1.6
	 */
	public static function boolean($i, $value, $taskOn = null, $taskOff = null)
	{
		// Load the behavior.
		self::behavior();
		JHtml::_('bootstrap.tooltip');

		// Build the title.
		$title = ($value) ? JText::_('JYES') : JText::_('JNO');
		$title = JHtml::tooltipText($title, JText::_('JGLOBAL_CLICK_TO_TOGGLE_STATE'), 0);

		// Build the <a> tag.
		$bool = ($value) ? 'true' : 'false';
		$task = ($value) ? $taskOff : $taskOn;
		$toggle = (!$task) ? false : true;

		if ($toggle)
		{
			return '<a class="grid_' . $bool . ' hasToolip" title="' . $title . '" rel="{id:\'cb' . $i . '\', task:\'' . $task
				. '\'}" href="#toggle"></a>';
		}
		else
		{
			return '<a class="grid_' . $bool . '"></a>';
		}
	}

	/**
	 * Method to sort a column in a grid
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   string  $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 * @param   string  $tip            An optional text shown as tooltip title instead of $title
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function sort($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc', $tip = '')
	{
		JHtml::_('bootstrap.tooltip');

		$direction = strtolower($direction);
		$icon = array('arrow-up-3', 'arrow-down-3');
		$index = (int) ($direction == 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}

		$html = '<a href="#" onclick="Joomla.tableOrdering(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\');return false;"'
			. ' class="hasTooltip" title="' . JHtml::tooltipText(($tip ? $tip : $title), 'JGLOBAL_CLICK_TO_SORT_THIS_COLUMN') . '">';

		if (isset($title['0']) && $title['0'] == '<')
		{
			$html .= $title;
		}
		else
		{
			$html .= JText::_($title);
		}

		if ($order == $selected)
		{
			$html .= ' <i class="icon-' . $icon[$index] . '"></i>';
		}

		$html .= '</a>';

		return $html;
	}

	/**
	 * Method to check all checkboxes in a grid
	 *
	 * @param   string  $name    The name of the form element
	 * @param   string  $tip     The text shown as tooltip title instead of $tip
	 * @param   string  $action  The action to perform on clicking the checkbox
	 *
	 * @return  string
	 *
	 * @since   3.1.2
	 */
	public static function checkall($name = 'checkall-toggle', $tip = 'JGLOBAL_CHECK_ALL', $action = 'Joomla.checkAll(this)')
	{
		JHtml::_('bootstrap.tooltip');

		return '<input type="checkbox" name="' . $name . '" value="" class="hasTooltip" title="' . JHtml::tooltipText($tip) . '" onclick="' . $action . '" />';
	}

	/**
	 * Method to create a checkbox for a grid row.
	 *
	 * @param   integer  $rowNum      The row index
	 * @param   integer  $recId       The record id
	 * @param   boolean  $checkedOut  True if item is checke out
	 * @param   string   $name        The name of the form element
	 *
	 * @return  mixed    String of html with a checkbox if item is not checked out, null if checked out.
	 *
	 * @since   1.5
	 */
	public static function id($rowNum, $recId, $checkedOut = false, $name = 'cid')
	{
		return $checkedOut ? '' : '<input type="checkbox" id="cb' . $rowNum . '" name="' . $name . '[]" value="' . $recId
			. '" onclick="Joomla.isChecked(this.checked);" />';
	}

	/**
	 * Displays a checked out icon.
	 *
	 * @param   object   &$row        A data object (must contain checkedout as a property).
	 * @param   integer  $i           The index of the row.
	 * @param   string   $identifier  The property name of the primary key or index of the row.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function checkedOut(&$row, $i, $identifier = 'id')
	{
		$user = JFactory::getUser();
		$userid = $user->get('id');

		if ($row instanceof JTable)
		{
			$result = $row->isCheckedOut($userid);
		}
		else
		{
			$result = false;
		}

		if ($result)
		{
			return self::_checkedOut($row);
		}
		else
		{
			if ($identifier == 'id')
			{
				return JHtml::_('grid.id', $i, $row->$identifier);
			}
			else
			{
				return JHtml::_('grid.id', $i, $row->$identifier, $result, $identifier);
			}
		}
	}

	/**
	 * Method to create a clickable icon to change the state of an item
	 *
	 * @param   mixed    $value   Either the scalar value or an object (for backward compatibility, deprecated)
	 * @param   integer  $i       The index
	 * @param   string   $img1    Image for a positive or on value
	 * @param   string   $img0    Image for the empty or off value
	 * @param   string   $prefix  An optional prefix for the task
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function published($value, $i, $img1 = 'tick.png', $img0 = 'publish_x.png', $prefix = '')
	{
		if (is_object($value))
		{
			$value = $value->published;
		}

		$img = $value ? $img1 : $img0;
		$task = $value ? 'unpublish' : 'publish';
		$alt = $value ? JText::_('JPUBLISHED') : JText::_('JUNPUBLISHED');
		$action = $value ? JText::_('JLIB_HTML_UNPUBLISH_ITEM') : JText::_('JLIB_HTML_PUBLISH_ITEM');

		return '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $prefix . $task . '\')" title="' . $action . '">'
			. JHtml::_('image', 'admin/' . $img, $alt, null, true) . '</a>';
	}

	/**
	 * Method to create a select list of states for filtering
	 * By default the filter shows only published and unpublished items
	 *
	 * @param   string  $filter_state  The initial filter state
	 * @param   string  $published     The JText string for published
	 * @param   string  $unpublished   The JText string for Unpublished
	 * @param   string  $archived      The JText string for Archived
	 * @param   string  $trashed       The JText string for Trashed
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function state($filter_state = '*', $published = 'Published', $unpublished = 'Unpublished', $archived = null, $trashed = null)
	{
		$state = array('' => '- ' . JText::_('JLIB_HTML_SELECT_STATE') . ' -', 'P' => JText::_($published), 'U' => JText::_($unpublished));

		if ($archived)
		{
			$state['A'] = JText::_($archived);
		}

		if ($trashed)
		{
			$state['T'] = JText::_($trashed);
		}

		return JHtml::_(
			'select.genericlist',
			$state,
			'filter_state',
			array(
				'list.attr' => 'class="inputbox" size="1" onchange="Joomla.submitform();"',
				'list.select' => $filter_state,
				'option.key' => null
			)
		);
	}

	/**
	 * Method to create an icon for saving a new ordering in a grid
	 *
	 * @param   array   $rows   The array of rows of rows
	 * @param   string  $image  The image [UNUSED]
	 * @param   string  $task   The task to use, defaults to save order
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function order($rows, $image = 'filesave.png', $task = 'saveorder')
	{
		return '<a href="javascript:saveorder(' . (count($rows) - 1) . ', \'' . $task . '\')" rel="tooltip" class="saveorder btn btn-micro pull-right" title="'
			. JText::_('JLIB_HTML_SAVE_ORDER') . '"><i class="icon-menu-2"></i></a>';
	}

	/**
	 * Method to create a checked out icon with optional overlib in a grid.
	 *
	 * @param   object   &$row     The row object
	 * @param   boolean  $overlib  True if an overlib with checkout information should be created.
	 *
	 * @return  string   HTMl for the icon and overlib
	 *
	 * @since   1.5
	 */
	protected static function _checkedOut(&$row, $overlib = true)
	{
		$hover = '';

		if ($overlib)
		{
			JHtml::_('bootstrap.tooltip');

			$date = JHtml::_('date', $row->checked_out_time, JText::_('DATE_FORMAT_LC1'));
			$time = JHtml::_('date', $row->checked_out_time, 'H:i');

			$hover = '<span class="editlinktip hasTooltip" title="' . JHtml::tooltipText('JLIB_HTML_CHECKED_OUT', $row->editor) . '<br />' . $date . '<br />'
				. $time . '">';
		}

		return $hover . JHtml::_('image', 'admin/checked_out.png', null, null, true) . '</span>';
	}

	/**
	 * Method to build the behavior script and add it to the document head.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function behavior()
	{
		static $loaded;

		if (!$loaded)
		{
			// Build the behavior script.
			$js = '
		window.addEvent(\'domready\', function(){
			actions = $$(\'a.move_up\');
			actions.combine($$(\'a.move_down\'));
			actions.combine($$(\'a.grid_true\'));
			actions.combine($$(\'a.grid_false\'));
			actions.combine($$(\'a.grid_trash\'));
			actions.each(function(a){
				a.addEvent(\'click\', function(){
					args = JSON.decode(this.rel);
					listItemTask(args.id, args.task);
				});
			});
			$$(\'input.check-all-toggle\').each(function(el){
				el.addEvent(\'click\', function(){
					if (el.checked) {
						document.id(this.form).getElements(\'input[type=checkbox]\').each(function(i){
							i.checked = true;
						})
					}
					else {
						document.id(this.form).getElements(\'input[type=checkbox]\').each(function(i){
							i.checked = false;
						})
					}
				});
			});
		});';

			// Add the behavior to the document head.
			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);

			$loaded = true;
		}
	}
}
