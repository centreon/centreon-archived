/* eslint-disable react/jsx-curly-newline */
/* eslint-disable react/jsx-indent */
/* eslint-disable react/jsx-wrap-multilines */
/* eslint-disable react/no-array-index-key */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React from 'react';

import clsx from 'clsx';

import { Switch, FormControlLabel, makeStyles } from '@material-ui/core';

import styles from '../global-sass-files/_grid.scss';
import Wrapper from '../Wrapper';
import SearchLive from '../Search/SearchLive';
import Button from '../Button';

import filterStyles from './top-filters.scss';

const useStyles = makeStyles({
  labelFontSize: {
    fontSize: '13px',
  },
});
const TopFilters = ({ fullText, switches, onChange }) => {
  const classes = useStyles();

  return (
    <div>
      <div className={filterStyles['filters-wrapper']}>
        <Wrapper>
          <div className={clsx(styles.container__row)}>
            {fullText ? (
              <div
                className={clsx(
                  styles['container__col-md-3'],
                  styles['container__col-xs-12'],
                )}
              >
                <SearchLive
                  filterKey={fullText.filterKey}
                  icon={fullText.icon}
                  label={fullText.label}
                  value={fullText.value}
                  onChange={onChange}
                />
              </div>
            ) : null}

            <div className={clsx(styles.container__row)}>
              {switches
                ? switches.map((switchColumn, index) => (
                    <div
                      className={filterStyles['switch-wrapper']}
                      key={`switchSubColumn${index}`}
                    >
                      {switchColumn.map(
                        (
                          {
                            switchTitle,
                            switchStatus,
                            button,
                            label,
                            buttonType,
                            color,
                            onClick,
                            value,
                            filterKey,
                          },
                          i,
                        ) =>
                          !button ? (
                            <FormControlLabel
                              classes={{ label: classes.labelFontSize }}
                              control={
                                <Switch
                                  checked={value}
                                  color="primary"
                                  size="small"
                                  onChange={(e) =>
                                    onChange(e.target.checked, filterKey)
                                  }
                                />
                              }
                              label={
                                <>
                                  {switchTitle && (
                                    <div>
                                      <b>{switchTitle}</b>
                                    </div>
                                  )}
                                  {switchStatus && <div>{switchStatus}</div>}
                                </>
                              }
                              labelPlacement="top"
                            />
                          ) : (
                            <div
                              className={clsx(
                                styles['container__col-sm-6'],
                                styles['container__col-xs-4'],
                                styles['center-vertical'],
                                styles['mt-1'],
                                filterStyles['button-wrapper'],
                              )}
                              key={`switch${index}${i}`}
                            >
                              <Button
                                buttonType={buttonType}
                                color={color}
                                key={`switchButton${index}${i}`}
                                label={label}
                                onClick={onClick}
                              />
                            </div>
                          ),
                      )}
                    </div>
                  ))
                : null}
            </div>
          </div>
        </Wrapper>
      </div>
    </div>
  );
};

export default TopFilters;
