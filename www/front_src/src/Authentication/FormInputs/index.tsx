import { useMemo } from 'react';

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
  propEq,
  reduce,
  sort,
  toPairs,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';
import { Divider, IconButton, Tooltip, Typography } from '@mui/material';

import {
  Category,
  InputProps,
  InputPropsWithoutCategory,
  InputType,
} from './models';
import MultipleInput from './Multiple';
import SwitchInput from './Switch';
import RadioInput from './Radio';
import TextInput from './Text';
import ConnectedAutocomplete from './ConnectedAutocomplete';
import FieldsTable from './FieldsTable';

export const getInput = cond<
  InputType,
  (props: InputPropsWithoutCategory) => JSX.Element
>([
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
  [
    equals(InputType.FieldsTable) as (b: InputType) => boolean,
    always(FieldsTable),
  ],
]);

const useStyles = makeStyles((theme) => ({
  additionalLabel: {
    marginBottom: theme.spacing(0.5),
  },
  category: {
    marginBottom: theme.spacing(2),
    marginTop: theme.spacing(2),
  },
  categoryTitle: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'flex',
    flexDirection: 'row',
  },
  inputWrapper: { width: '100%' },
  inputs: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(1),
    rowGap: theme.spacing(2),
  },
  tooltip: {
    maxWidth: theme.spacing(60),
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

  const inputsByCategory = useMemo(
    () =>
      groupBy(
        ({ category }) => find(equals(category), categoriesName) as string,
        inputs,
      ),
    [inputs],
  );

  const sortedCategoryNames = useMemo(() => {
    const sortedCategories = sort(ascend(prop('order')), categories);

    const usedCategories = filter(
      ({ name }) => any(equals(name), keys(inputsByCategory)),
      sortedCategories,
    );

    return pluck('name', usedCategories);
  }, []);

  const sortedInputsByCategory = useMemo(
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

  const lastCategory = useMemo(() => last(sortedCategoryNames), []);

  return (
    <div>
      {toPairs(sortedInputsByCategory).map(
        ([categoryName, categorizedInputs]) => {
          const { EndIcon, TooltipContent } = find(
            propEq('name', categoryName),
            categories,
          ) as Category;

          return (
            <div key={categoryName}>
              <div className={classes.category}>
                <div className={classes.categoryTitle}>
                  <Typography variant="h5">{t(categoryName)}</Typography>
                  <Tooltip
                    classes={{
                      tooltip: classes.tooltip,
                    }}
                    placement="top"
                    title={TooltipContent ? <TooltipContent /> : ''}
                  >
                    <IconButton size="small">
                      {EndIcon && <EndIcon fontSize="small" />}
                    </IconButton>
                  </Tooltip>
                </div>
                <div className={classes.inputs}>
                  {categorizedInputs.map((inputProps) => {
                    const Input = getInput(inputProps.type);

                    return (
                      <div
                        className={classes.inputWrapper}
                        key={inputProps.label}
                      >
                        {inputProps.additionalLabel && (
                          <Typography
                            className={classes.additionalLabel}
                            variant="body1"
                          >
                            {t(inputProps.additionalLabel)}
                          </Typography>
                        )}
                        <Input {...inputProps} />
                      </div>
                    );
                  })}
                </div>
              </div>
              {not(equals(lastCategory, categoryName)) && <Divider />}
            </div>
          );
        },
      )}
    </div>
  );
};

export default Inputs;
