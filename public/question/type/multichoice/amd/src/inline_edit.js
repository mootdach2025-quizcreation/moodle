// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Manages inline editing of question name
 *
 * @module     qtype_multichoice/inline_edit
 * @copyright  2025 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import {call as fetchMany} from 'core/ajax';
import MoodleConfig from 'core/config';
// import {addIconToContainer} from 'core/loadingicon';
import Notification from 'core/notification';
import Pending from 'core/pending';
// import {get_string as getString} from 'core/str';
import {render as renderTemplate} from 'core/templates';
import {replaceNodeContents} from 'core/templates';

const SELECTORS = {
    'editableItem': 'span.inplaceeditable',
};

/**
 * Removes the edit UI from an editable item.
 *
 * @param {HTMLElement} editableItem the editable to turn off.
 */
const stopEditingItem = (editableItem) => {
    editableItem.innerHTML = editableItem.dataset.oldContent;
    delete editableItem.dataset.oldContent;

    editableItem.classList.remove('inplaceeditingon');
    editableItem.querySelector('a').focus();
};

/**
 * Handle clicks in the table the shows the grade items.
 *
 * @param {Event} e click event.
 */
const handleItemClick = async (e) => {
    const editableItem = e.target.closest(SELECTORS.editableItem);

    // Check this click is on a relevant element.
    if (!editableItem) {
        return;
    }

    e.preventDefault();
    const pending = new Pending('edit-question-item-start');

    // TODO document.querySelectorAll(SELECTORS.inplaceEditableOn).forEach(stopEditingGadeItem);

    editableItem.dataset.oldContent = editableItem.innerHTML;

    renderTemplate('qtype_multichoice/editing_item', {
            "uniqueid": "question-name",
            "editlablekey": editableItem.dataset.editLabel,
            "rawvalue": editableItem.dataset.rawValue,
        }
    ).then((html, js) => {
        replaceNodeContents(editableItem, html, js || '');
        const inputElement = editableItem.querySelector('input');
        inputElement.focus();
        inputElement.select();
        editableItem.classList.add('inplaceeditingon');
        pending.resolve();
        return null;
    }).catch(Notification.exception);
};

/**
 * Handle key up in the editable - used to make Esc cancel.
 *
 * @param {Event} e key event.
 */
const handleItemKeyUp = (e) => {
    if (e.keyCode !== 27) {
        return;
    }

    const editableItem = e.target.closest(SELECTORS.editableItem);

    // Check this click is on a relevant element.
    if (!editableItem) {
        return;
    }

    e.preventDefault();
    stopEditingItem(editableItem);
};

/**
 * Handle focus out of the editable.
 *
 * @param {Event} e event.
 */
const handleItemFocusOut = (e) => {
    if (MoodleConfig.behatsiterunning) {
        // Behat triggers focusout too often so ignore.
        return;
    }

    const editableItem = e.target.closest(SELECTORS.editableItem);

    // Check this click is on a relevant element.
    if (!editableItem) {
        return;
    }

    e.preventDefault();
    stopEditingItem(editableItem);
};

/**
 * Initialise all the even handlers.
 */
const registerEventListeners = () => {
    document.body.addEventListener('click', handleItemClick);
    // document.body.addEventListener('keydown', handleGradeItemKeyDown);
    document.body.addEventListener('keyup', handleItemKeyUp);
    document.body.addEventListener('focusout', handleItemFocusOut);
};

/**
 * Entry point.
 */
export const init = () => {
    registerEventListeners();
};
