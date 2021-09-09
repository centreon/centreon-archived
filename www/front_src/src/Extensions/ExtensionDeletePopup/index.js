/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React from 'react';

import clsx from 'clsx';

import styles from '../Popup/popup.scss';
import Popup from '../Popup';
import Title from '../Title';
import MessageInfo from '../Message/MessageInfo';
import Button from '../Button';
import IconClose from '../Icon/IconClose';

class ExtensionDeletePopup extends React.Component {
  render() {
    const { deletingEntity, onConfirm, onCancel } = this.props;

    return (
      <Popup popupType="small">
        <div className={clsx(styles['popup-header'])}>
          <Title label={deletingEntity.description} />
        </div>
        <div className={clsx(styles['popup-body'])}>
          <MessageInfo
            messageInfo="red"
            text="Do you want to delete this extension? This action will remove all associated data."
          />
        </div>
        <div className={clsx(styles['popup-footer'])}>
          <div className={clsx(styles.container__row)}>
            <div className={clsx(styles['container__col-xs-6'])}>
              <Button
                buttonType="regular"
                color="red"
                label="Delete"
                onClick={(e) => {
                  e.preventDefault();
                  e.stopPropagation();
                  onConfirm(deletingEntity.id, deletingEntity.type);
                }}
              />
            </div>
            <div className={clsx(styles['container__col-xs-6'], ['text-left'])}>
              <Button
                buttonType="regular"
                color="gray"
                label="Cancel"
                onClick={(e) => {
                  e.preventDefault();
                  e.stopPropagation();
                  onCancel();
                }}
              />
            </div>
          </div>
        </div>
        <IconClose
          iconPosition="icon-close-position-middle"
          iconType="middle"
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            onCancel();
          }}
        />
      </Popup>
    );
  }
}

export default ExtensionDeletePopup;
