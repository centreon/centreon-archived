/* eslint-disable no-alert */
import React from 'react';

import clsx from 'clsx';

import Subtitle from '../Subtitle';
import IconInfo from '../Icon/IconInfo';
import Title from '../Title';
import Button from '../Button';
import ButtonAction from '../Button/ButtonAction';

import styles from './card.scss';
import CardItem from './CardItem';

import Card from '.';

export default { title: 'Card' };

export const withoutContent = () => (
  <Card>
    <CardItem
      itemBorderColor="orange"
      itemFooterColor="orange"
      itemFooterLabel="Some label for the footer"
      style={{
        width: '250px',
      }}
    />
  </Card>
);

export const withContent = () => (
  <Card>
    <CardItem
      itemBorderColor="orange"
      itemFooterColor="orange"
      itemFooterLabel="Some label for the footer"
      style={{
        width: '250px',
      }}
      onClick={() => {
        alert('Card clicked- open popin');
      }}
    >
      <IconInfo
        iconColor="green"
        iconName="state"
        iconPosition="info-icon-position"
      />
      <div className={clsx(styles['custom-title-heading'])}>
        <Title
          customTitleStyles="custom-title-styles"
          icon="object"
          label="Test Title"
          onClick={() => {
            alert('Card clicked- open popin');
          }}
        />
        <Subtitle
          customSubtitleStyles="custom-subtitle-styles"
          label="Test Subtitle"
          onClick={() => {
            alert('Card clicked- open popin');
          }}
        />
      </div>
      <Button
        buttonType="regular"
        color="orange"
        iconActionType="update"
        iconColor="white"
        iconPosition="icon-right"
        label="Button example"
        position="button-card-position"
        onClick={() => {
          alert('Button clicked');
        }}
      />
      <ButtonAction
        buttonActionType="delete"
        buttonIconType="delete"
        customPosition="button-action-card-position"
        iconColor="gray"
        iconPosition="icon-right"
        onClick={() => {
          alert('Button delete clicked');
        }}
      />
    </CardItem>
  </Card>
);
