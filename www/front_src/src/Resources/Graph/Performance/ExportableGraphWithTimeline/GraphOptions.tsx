import * as React from 'react';

import { isNil, not, values } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  FormControlLabel,
  FormGroup,
  makeStyles,
  Popover,
  Switch,
} from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';

import { IconButton } from '@centreon/ui';

import { labelGraphOptions } from '../../../translatedLabels';

import { useGraphOptionsContext } from './useGraphOptions';

const useStyles = makeStyles((theme) => ({
  popoverContent: {
    margin: theme.spacing(1, 2),
  },
  optionLabel: {
    margin: 0,
    justifyContent: 'space-between',
  },
}));

const GraphOptions = (): JSX.Element => {
  const [anchorEl, setAnchorEl] = React.useState<Element | null>(null);
  const { graphOptions, changeGraphOptions } = useGraphOptionsContext();
  const classes = useStyles();
  const { t } = useTranslation();

  const openGraphOptions = (event: React.MouseEvent) => {
    if (isNil(anchorEl)) {
      setAnchorEl(event.currentTarget);
      return;
    }
    setAnchorEl(null);
  };

  const closeGraphOptions = () => setAnchorEl(null);

  const graphOptionsConfiguration = values(graphOptions);

  return (
    <>
      <IconButton
        title={t(labelGraphOptions)}
        ariaLabel={t(labelGraphOptions)}
        onClick={openGraphOptions}
        size="small"
      >
        <SettingsIcon style={{ fontSize: 18 }} />
      </IconButton>
      <Popover
        open={not(isNil(anchorEl))}
        anchorEl={anchorEl}
        onClose={closeGraphOptions}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'center',
        }}
      >
        <FormGroup className={classes.popoverContent}>
          {graphOptionsConfiguration.map(({ label, value, id }) => (
            <FormControlLabel
              key={label}
              control={
                <Switch
                  size="small"
                  checked={value}
                  onChange={changeGraphOptions(id)}
                  color="primary"
                />
              }
              label={t(label)}
              labelPlacement="start"
              className={classes.optionLabel}
            />
          ))}
        </FormGroup>
      </Popover>
    </>
  );
};

export default GraphOptions;
