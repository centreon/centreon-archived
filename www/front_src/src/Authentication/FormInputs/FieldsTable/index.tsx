import { FormikValues, useFormikContext } from 'formik';
import { equals, length, pick, pipe, prop, type } from 'ramda';
import { useAtomValue } from 'jotai';
import { useTranslation } from 'react-i18next';

import { FormHelperText, Theme, Typography } from '@mui/material';
import { CreateCSSProperties, makeStyles } from '@mui/styles';

import { useMemoComponent } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { InputPropsWithoutCategory } from '../models';

import Row from './Row';

const useStyles = makeStyles<Theme, { columns }, string>((theme) => ({
  container: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(1),
  },
  icon: {
    marginTop: theme.spacing(0.5),
  },
  inputsRow: ({ columns }): CreateCSSProperties => ({
    columnGap: theme.spacing(2),
    display: 'grid',
    gridTemplateColumns: `repeat(${columns}, 1fr) min-content`,
  }),
  table: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(2),
  },
}));

const FieldsTable = ({
  fieldsTableConfiguration,
  fieldName,
  label,
  additionalFieldsToMemoize,
}: InputPropsWithoutCategory): JSX.Element => {
  const classes = useStyles({
    columns: fieldsTableConfiguration?.columns.length,
  });
  const { t } = useTranslation();

  const { themeMode } = useAtomValue(userAtom);

  const { values, errors } = useFormikContext<FormikValues>();

  const tableValues = prop(fieldName, values);

  const fieldsTableError = prop(fieldName, errors) as string | undefined;

  const fieldsToMemoize =
    additionalFieldsToMemoize && pick(additionalFieldsToMemoize, values);

  return useMemoComponent({
    Component: (
      <div className={classes.container}>
        <Typography>{t(label)}</Typography>
        <div className={classes.table}>
          {[...Array(tableValues.length + 1).keys()].map((idx): JSX.Element => {
            const getRequired = (): boolean =>
              fieldsTableConfiguration?.getRequired?.({ index: idx, values }) ||
              false;

            const isLastElement = pipe(
              length as (list) => number,
              equals(idx),
            )(tableValues);

            return (
              <Row
                columns={fieldsTableConfiguration?.columns}
                defaultRowValue={fieldsTableConfiguration?.defaultRowValue}
                deleteLabel={fieldsTableConfiguration?.deleteLabel}
                getRequired={getRequired}
                index={idx}
                isLastElement={isLastElement}
                key={`${label}_${idx}`}
                label={label}
                tableFieldName={fieldName}
              />
            );
          })}
        </div>
        {equals(type(fieldsTableError), 'String') && (
          <FormHelperText error>{fieldsTableError}</FormHelperText>
        )}
      </div>
    ),
    memoProps: [tableValues, fieldsTableError, themeMode, fieldsToMemoize],
  });
};

export default FieldsTable;
