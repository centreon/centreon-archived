import * as React from 'react';

import { isNil, not, pluck, values } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  FormControlLabel,
  FormGroup,
  makeStyles,
  Popover,
  Switch,
} from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';

import { IconButton, useMemoComponent } from '@centreon/ui';

import { labelGraphOptions } from '../../../translatedLabels';
import { GraphOption } from '../../../Details/models';

import { useGraphOptionsContext } from './useGraphOptions';

const useStyles = makeStyles((theme) => ({
  optionLabel: {
    justifyContent: 'space-between',
    margin: 0,
  },
  popoverContent: {
    margin: theme.spacing(1, 2),
  },
}));

const GraphOptions = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [anchorEl, setAnchorEl] = React.useState<Element | null>(null);
  const { graphOptions, changeGraphOptions } = useGraphOptionsContext();

  const openGraphOptions = (event: React.MouseEvent): void => {
    if (isNil(anchorEl)) {
      setAnchorEl(event.currentTarget);

      return;
    }
    setAnchorEl(null);
  };

  const closeGraphOptions = (): void => setAnchorEl(null);

  const graphOptionsConfiguration = values(graphOptions);

  const graphOptionsConfigurationValue = pluck<keyof GraphOption, GraphOption>(
    'value',
    graphOptionsConfiguration,
  );

  return useMemoComponent({
    Component: (
      <>
        <IconButton
          ariaLabel={t(labelGraphOptions)}
          size="small"
          title={t(labelGraphOptions)}
          onClick={openGraphOptions}
        >
          <SettingsIcon style={{ fontSize: 18 }} />
        </IconButton>
        <Popover
          anchorEl={anchorEl}
          anchorOrigin={{
            horizontal: 'center',
            vertical: 'bottom',
          }}
          open={not(isNil(anchorEl))}
          onClose={closeGraphOptions}
        >
          <FormGroup className={classes.popoverContent}>
            {graphOptionsConfiguration.map(({ label, value, id }) => (
              <FormControlLabel
                className={classes.optionLabel}
                control={
                  <Switch
                    checked={value}
                    color="primary"
                    size="small"
                    onChange={changeGraphOptions(id)}
                  />
                }
                data-testid={label}
                key={label}
                label={t(label)}
                labelPlacement="start"
              />
            ))}
          </FormGroup>
        </Popover>
      </>
    ),
    memoProps: [graphOptionsConfigurationValue, anchorEl],
  });
};

export default GraphOptions;
