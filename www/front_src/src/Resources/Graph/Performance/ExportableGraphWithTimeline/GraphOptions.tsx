import { pluck, values } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { FormControlLabel, FormGroup, Switch } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';

import { GraphOption, GraphOptions } from '../../../Details/models';
import {
  setGraphTabParametersDerivedAtom,
  tabParametersAtom,
} from '../../../Details/detailsAtoms';

import {
  changeGraphOptionsDerivedAtom,
  graphOptionsAtom,
} from './graphOptionsAtoms';

const useStyles = makeStyles(() => ({
  optionLabel: {
    justifyContent: 'space-between',
    margin: 0,
  },
}));

const Options = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const graphOptions = useAtomValue(graphOptionsAtom);
  const tabParameters = useAtomValue(tabParametersAtom);
  const changeGraphOptions = useUpdateAtom(changeGraphOptionsDerivedAtom);
  const setGraphTabParameters = useUpdateAtom(setGraphTabParametersDerivedAtom);

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
      <FormGroup>
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
            labelPlacement="bottom"
          />
        ))}
      </FormGroup>
    ),
    memoProps: [graphOptionsConfigurationValue],
  });
};

export default Options;
