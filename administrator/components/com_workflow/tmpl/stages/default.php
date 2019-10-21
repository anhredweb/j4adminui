<?php
/**
 * Items Model for a Workflow Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$userId    = $user->id;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrderingUrl = '';

$saveOrder = ($listOrder == 's.ordering');

$isCore = $this->workflow->core;

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_workflow&task=stages.saveOrderAjax&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->escape($this->extension) . '&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_workflow&view=stages&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php if (!empty($this->sidebar)) : ?>
			<div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
			</div>
		<?php endif; ?>
		<div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->stages)) : ?>
					<div class="j-alert j-alert-info d-flex mt-4">
						<div class="j-alert-icon-wrap"><span class="icon-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span></div>
						<div class="j-alert-info-wrap"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
					</div>
				<?php else: ?>
					<table class="table j-list-table">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_WORKFLOW_STAGES_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center hidden-sm-down">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="text-center hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', '', 's.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-caret-v'); ?>
								</th>
								<th scope="col" style="width:1%" class="text-center hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 's.condition', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:1%" class="text-center hidden-sm-down">
									<?php echo Text::_('COM_WORKFLOW_DEFAULT'); ?>
								</th>
								<th scope="col" style="width:10%" class="hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_NAME', 's.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_CONDITION', 's.condition', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:1%" class="hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_ID', 's.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>">
							<?php foreach ($this->stages as $i => $item):
								$edit = Route::_('index.php?option=com_workflow&task=stage.edit&id=' . $item->id . '&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension);

								$canEdit    = $user->authorise('core.edit', $this->extension . '.stage.' . $item->id);
								// @TODO set proper checkin fields
								$canCheckin = true || $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
								$canChange  = $user->authorise('core.edit.stage', $this->extension . '.stage.' . $item->id) && $canCheckin;
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center hidden-sm-down">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center hidden-sm-down">
										<?php
										$iconClass = '';
										if (!$canChange)
										{
											$iconClass = ' inactive';
										}
										elseif (!$saveOrder)
										{
											$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler icon-move-v<?php echo $iconClass ?>">
											<span class="icon-arrows-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
										<?php endif; ?>									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'stages.', $canChange && !$isCore); ?>
										</div>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.isdefault', $item->default, $i, 'stages.', $canChange); ?>
									</td>
									<th scope="row">
										<?php if ($canEdit && !$isCore) : ?>
											<a href="<?php echo $edit; ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes(Text::_($item->title))); ?>">
												<?php echo $this->escape(Text::_($item->title)); ?>
											</a>
											<div class="small"><?php echo $this->escape(Text::_($item->description)); ?></div>
										<?php else: ?>
											<?php echo $this->escape(Text::_($item->title)); ?>
											<div class="small"><?php echo $this->escape(Text::_($item->description)); ?></div>
										<?php endif; ?>
									</th>
									<td class="nowrap">
										<?php
											if ($item->condition == 'JARCHIVED'):
												$icon = 'icon-archive';
											elseif ($item->condition == 'JTRASHED'):
												$icon = 'icon-trash';
											elseif ($item->condition == 'JPUBLISHED'):
												$icon = 'icon-publish';
											elseif ($item->condition == 'JUNPUBLISHED'):
												$icon = 'icon-unpublish';
											endif;
										?>
										<span class="<?php echo $icon; ?>" aria-hidden="true"></span>
										<?php echo Text::_($item->condition); ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo (int) $item->id; ?>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
					<!-- load the pagination. -->
					<div class="j-pagination-footer">
						<?php echo LayoutHelper::render('joomla.searchtools.default.listlimit', array('view' => $this)); ?>
						<?php echo $this->pagination->getListFooter(); ?>
					</div>

				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="workflow_id" value="<?php echo (int) $this->workflowID ?>">
				<input type="hidden" name="extension" value="<?php echo $this->extension ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
