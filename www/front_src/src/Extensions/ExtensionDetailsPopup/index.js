/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React from 'react';

import clsx from 'clsx';

import styles from '../Popup/popup.scss';
import Popup from '../Popup';
import Slider from '../Slider/SliderContent';
import IconContent from '../Icon/IconContent';
import Title from '../Title';
// import Button from '../Button';
import HorizontalLine from '../HorizontalLines';
import Description from '../Description';

import {
  SliderSkeleton,
  HeaderSkeleton,
  ContentSkeleton,
  ReleaseNoteSkeleton,
} from './LoadingSkeleton';

class ExtensionDetailPopup extends React.Component {
  render() {
    const {
      type,
      modalDetails,
      onCloseClicked,
      onDeleteClicked,
      onUpdateClicked,
      onInstallClicked,
      loading,
      animate,
    } = this.props;

    if (modalDetails === null) {
      return null;
    }

    return (
      <Popup popupType="big">
        {loading ? (
          <SliderSkeleton animate={animate} />
        ) : (
          <Slider
            images={!loading && modalDetails.images ? modalDetails.images : []}
            type={type}
          >
            {modalDetails.version.installed && modalDetails.version.outdated ? (
              <IconContent
                customClass="content-icon-popup-wrapper"
                iconContentColor="orange"
                iconContentType="update"
                onClick={() => {
                  onUpdateClicked(modalDetails.id, modalDetails.type);
                }}
              />
            ) : null}
            {modalDetails.version.installed ? (
              <IconContent
                customClass="content-icon-popup-wrapper"
                iconContentColor="red"
                iconContentType="delete"
                onClick={() => {
                  onDeleteClicked(modalDetails.id, modalDetails.type);
                }}
              />
            ) : (
              <IconContent
                customClass="content-icon-popup-wrapper"
                iconContentColor="green"
                iconContentType="add"
                onClick={() => {
                  onInstallClicked(modalDetails.id, modalDetails.type);
                }}
              />
            )}
          </Slider>
        )}
        <div className={clsx(styles['popup-header'])}>
          {loading ? (
            <HeaderSkeleton animate={animate} />
          ) : (
            <>
              <Title label={modalDetails.title} />
              <Button
                buttonType="regular"
                color="blue"
                label={
                  (!modalDetails.version.installed ? 'Available ' : '') +
                  modalDetails.version.available
                }
                style={{ cursor: 'default' }}
              />
              <Button
                buttonType="bordered"
                color="gray"
                label={modalDetails.stability}
                style={{ cursor: 'default', margin: '15px' }}
              />
            </>
          )}
        </div>
        <HorizontalLine />
        <div className={clsx(styles['popup-body'])}>
          {loading ? (
            <ContentSkeleton animate={animate} />
          ) : (
            <>
              {modalDetails.last_update ? (
                <Description date={`Last update ${modalDetails.last_update}`} />
              ) : null}
              <Description title="Description:" />
              <Description text={modalDetails.description} />
            </>
          )}
        </div>
        <HorizontalLine />
        <div className={clsx(styles['popup-footer'])}>
          {loading ? (
            <ReleaseNoteSkeleton animate={animate} />
          ) : (
            <Description link note={modalDetails.release_note} />
          )}
        </div>
        <IconClose
          iconPosition="icon-close-position-big"
          iconType="big"
          onClick={onCloseClicked}
        />
      </Popup>
    );
  }
}

export default ExtensionDetailPopup;
