import * as React from 'react';

import { isNil, not, pluck, values } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { FormControlLabel, FormGroup, Popover, Switch } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import SettingsIcon from '@mui/icons-material/Settings';

import { IconButton, useMemoComponent } from '@centreon/ui';

import { labelGraphOptions } from '../../../translatedLabels';
import { GraphOption, GraphOptions } from '../../../Details/models';
import {
  setGraphTabParametersDerivedAtom,
  tabParametersAtom,
} from '../../../Details/detailsAtoms';

import {
  changeGraphOptionsDerivedAtom,
  graphOptionsAtom,
} from './graphOptionsAtoms';

const useStyles = makeStyles((theme) => ({
  optionLabel: {
    justifyContent: 'space-between',
    margin: 0,
  },
  popoverContent: {
    margin: theme.spacing(1, 2),
  },
}));

const Options = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [anchorEl, setAnchorEl] = React.useState<Element | null>(null);

  const graphOptions = useAtomValue(graphOptionsAtom);
  const tabParameters = useAtomValue(tabParametersAtom);
  const changeGraphOptions = useUpdateAtom(changeGraphOptionsDerivedAtom);
  const setGraphTabParameters = useUpdateAtom(setGraphTabParametersDerivedAtom);

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

  const changeTabGraphOptions = (options: GraphOptions): void => {
    setGraphTabParameters({
      ...tabParameters.graph,
      options,
    });
  };

  return useMemoComponent({
    Component: (
      <>
        <IconButton
          ariaLabel={t(labelGraphOptions)}
          data-testid={labelGraphOptions}
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
                    onChange={(): void =>
                      changeGraphOptions({
                        changeTabGraphOptions,
                        graphOptionId: id,
                      })
                    }
                  />
                }
                data-testid={label}
                key={label}
                label={t(label) as string}
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

export default Options;
