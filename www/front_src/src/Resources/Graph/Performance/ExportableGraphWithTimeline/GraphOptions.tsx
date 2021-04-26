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
  optionLabel: {
    justifyContent: 'space-between',
    margin: 0,
  },
  popoverContent: {
    margin: theme.spacing(1, 2),
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
              key={label}
              label={t(label)}
              labelPlacement="start"
            />
          ))}
        </FormGroup>
      </Popover>
    </>
  );
};

export default GraphOptions;
