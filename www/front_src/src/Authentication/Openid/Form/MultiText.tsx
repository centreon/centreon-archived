import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { isNil, pluck, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';

import {
  DraggableAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import { InputProps } from '../models';

const useStyles = makeStyles({
  errorStack: {
    display: 'flex',
    flexDirection: 'column',
  },
});

const MultiText = ({
  fieldName,
  label,
  getDisabled,
}: InputProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

  const change = (selectedValues: Array<SelectEntry>): void => {
    setFieldValue(fieldName, pluck('name', selectedValues));
  };

  const initialValue = React.useMemo(() => {
    return prop(fieldName, values).map((value) => ({
      createOption: value,
      id: value,
      name: value,
    }));
  }, []);

  const getError = (): JSX.Element | undefined => {
    const error =
      (prop(fieldName, errors) as Array<string> | undefined)
        ?.map((errorText, index) => {
          if (isNil(errorText)) {
            return undefined;
          }

          return (
            <span key={errorText}>{`${value.at(index)}: ${errorText}`}</span>
          );
        })
        .filter(Boolean) || undefined;

    return error ? (
      <span className={classes.errorStack}>{error}</span>
    ) : undefined;
  };

  const value = prop(fieldName, values);
  const disabled = getDisabled?.(values);

  return useMemoComponent({
    Component: (
      <DraggableAutocompleteField
        disabled={getDisabled?.(values)}
        error={getError()}
        initialValues={initialValue}
        label={t(label)}
        name={fieldName}
        options={[]}
        popupIcon={null}
        onSelectedValuesChange={change}
      />
    ),
    memoProps: [value, getError(), disabled],
  });
};

export default MultiText;
