/* eslint-disable no-console */
import React from 'react';

import clsx from 'clsx';

import styles from '../global-sass-files/_grid.scss';

import TopFilters from '.';

export default { title: 'TopFilters' };

export const normal = () => (
  <TopFilters
    fullText={{
      label: 'Search:',
      onChange: (a) => {
        console.log(a);
      },
    }}
    switches={[
      [
        {
          customClass: clsx(
            styles['container__col-md-3'],
            styles['container__col-xs-4'],
          ),
          defaultValue: false,
          onChange: (value) => {
            console.log(value);
          },
          switchStatus: 'Not installed',
          switchTitle: 'Status:',
        },
        {
          customClass: clsx(
            styles['container__col-md-3'],
            styles['container__col-xs-4'],
          ),
          defaultValue: false,
          onChange: (value) => {
            console.log(value);
          },
          switchStatus: 'Installed',
        },
        {
          customClass: clsx(
            styles['container__col-md-3'],
            styles['container__col-xs-4'],
          ),
          defaultValue: false,
          onChange: (value) => {
            console.log(value);
          },
          switchStatus: 'Update',
        },
      ],
      [
        {
          customClass: clsx(
            styles['container__col-sm-3'],
            styles['container__col-xs-4'],
          ),
          defaultValue: false,
          onChange: (value) => {
            console.log(value);
          },
          switchStatus: 'Module',
          switchTitle: 'Type:',
        },
        {
          customClass: clsx(
            styles['container__col-sm-3'],
            styles['container__col-xs-4'],
          ),
          defaultValue: false,
          onChange: (value) => {
            console.log(value);
          },
          switchStatus: 'Update',
        },
        {
          button: true,
          buttonType: 'bordered',
          color: 'black',
          label: 'Clear Filters',
          onClick: () => {
            console.log('Clear filters clicked');
          },
        },
      ],
    ]}
  />
);
