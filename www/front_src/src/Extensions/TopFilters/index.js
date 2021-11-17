/* eslint-disable no-nested-ternary */
/* eslint-disable react/jsx-curly-newline */
/* eslint-disable react/jsx-indent */
/* eslint-disable react/jsx-wrap-multilines */
/* eslint-disable react/no-array-index-key */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React from 'react';

import { Switch, FormControlLabel, Button, Typography } from '@mui/material';

import makeStyles from '@mui/styles/makeStyles';

import { SearchField } from '@centreon/ui';

import Wrapper from '../Wrapper';

const useStyles = makeStyles({
  labelFontSize: {
    fontSize: '13px',
  },
});
const TopFilters = ({ fullText, switches, onChange }) => {
  const classes = useStyles();

  return (
    <Wrapper>
      <div style={{ alignItems: 'center', display: 'flex' }}>
        {fullText ? (
          <SearchField
            style={{ flexGrow: 0.5 }}
            value={fullText.value}
            onChange={(e) => onChange(e.target.value, fullText.filterKey)}
          />
        ) : null}

        <div style={{ alignItems: 'center', display: 'flex' }}>
          {switches
            ? switches.map((switchColumn, index) => (
                <div
                  key={`switchSubColumn${index}`}
                  style={{ alignItems: 'center', display: 'flex' }}
                >
                  {switchColumn.map(
                    (
                      {
                        switchTitle,
                        switchStatus,
                        button,
                        label,
                        onClick,
                        value,
                        filterKey,
                      },
                      i,
                    ) =>
                      !button ? (
                        switchTitle ? (
                          <Typography
                            key={switchTitle}
                            style={{
                              fontWeight: 'bold',
                              marginLeft: 16,
                            }}
                            variant="body1"
                          >
                            {switchTitle}
                          </Typography>
                        ) : (
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
                            key={switchStatus}
                            label={
                              <div
                                style={{
                                  alignItems: 'center',
                                  display: 'flex',
                                }}
                              >
                                {switchStatus && <div>{switchStatus}</div>}
                              </div>
                            }
                            labelPlacement="top"
                          />
                        )
                      ) : (
                        <div key={`switch${index}${i}`}>
                          <Button
                            color="primary"
                            size="small"
                            variant="contained"
                            onClick={onClick}
                          >
                            {label}
                          </Button>
                        </div>
                      ),
                  )}
                </div>
              ))
            : null}
        </div>
      </div>
    </Wrapper>
  );
};

export default TopFilters;
