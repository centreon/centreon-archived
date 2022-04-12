import * as React from 'react';

import {
  always,
  any,
  ascend,
  cond,
  equals,
  filter,
  find,
  groupBy,
  keys,
  last,
  not,
  pluck,
  prop,
  reduce,
  sort,
  toPairs,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';
import { Divider, Typography } from '@mui/material';

import { Category, InputProps, InputType } from './models';
import MultipleInput from './Multiple';
import SwitchInput from './Switch';
import RadioInput from './Radio';
import TextInput from './Text';
import ConnectedAutocomplete from './ConnectedAutocomplete';

export const getInput = cond<InputType, (props: InputProps) => JSX.Element>([
  [equals(InputType.Switch) as (b: InputType) => boolean, always(SwitchInput)],
  [equals(InputType.Radio) as (b: InputType) => boolean, always(RadioInput)],
  [equals(InputType.Text) as (b: InputType) => boolean, always(TextInput)],
  [
    equals(InputType.Multiple) as (b: InputType) => boolean,
    always(MultipleInput),
  ],
  [equals(InputType.Password) as (b: InputType) => boolean, always(TextInput)],
  [
    equals(InputType.ConnectedAutocomplete) as (b: InputType) => boolean,
    always(ConnectedAutocomplete),
  ],
]);

const useStyles = makeStyles((theme) => ({
  category: {
    marginBottom: theme.spacing(2),
    marginTop: theme.spacing(2),
  },
  inputs: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(1),
    rowGap: theme.spacing(2),
  },
}));

interface Props {
  categories: Array<Category>;
  inputs: Array<InputProps>;
}

const Inputs = ({ inputs, categories }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const categoriesName = pluck('name', categories);

  const inputsByCategory = React.useMemo(
    () =>
      groupBy(
        ({ category }) => find(equals(category), categoriesName) as string,
        inputs,
      ),
    [inputs],
  );

  const sortedCategoryNames = React.useMemo(() => {
    const sortedCategories = sort(ascend(prop('order')), categories);

    const usedCategories = filter(
      ({ name }) => any(equals(name), keys(inputsByCategory)),
      sortedCategories,
    );

    return pluck('name', usedCategories);
  }, []);

  const sortedInputsByCategory = React.useMemo(
    () =>
      reduce<string, Record<string, Array<InputProps>>>(
        (acc, value) => ({
          ...acc,
          [value]: sort(
            (a, b) => (b?.required ? 1 : 0) - (a?.required ? 1 : 0),
            inputsByCategory[value],
          ),
        }),
        {},
        sortedCategoryNames,
      ),
    [inputs],
  );

  const lastCategory = React.useMemo(() => last(sortedCategoryNames), []);

  return (
    <div>
      {toPairs(sortedInputsByCategory).map(([category, categorizedInputs]) => (
        <div key={category}>
          <div className={classes.category}>
            <Typography variant="h5">{t(category)}</Typography>
            <div className={classes.inputs}>
              {categorizedInputs.map(
                ({
                  fieldName,
                  label,
                  type,
                  options,
                  change,
                  getChecked,
                  required,
                  getDisabled,
                  getRequired,
                }) => {
                  const Input = getInput(type);

                  const props = {
                    category,
                    change,
                    fieldName,
                    getChecked,
                    getDisabled,
                    getRequired,
                    label,
                    options,
                    required,
                    type,
                  };

                  return <Input key={label} {...props} />;
                },
              )}
            </div>
          </div>
          {not(equals(lastCategory, category)) && <Divider />}
        </div>
      ))}
    </div>
  );
};

export default Inputs;
