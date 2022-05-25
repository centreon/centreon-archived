import { FormikValues, useFormikContext } from 'formik';
import { equals, length, pipe, prop, type } from 'ramda';
import { useAtomValue } from 'jotai';

import { FormHelperText, Theme } from '@mui/material';
import { CreateCSSProperties, makeStyles } from '@mui/styles';

import { useMemoComponent } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { InputPropsWithoutCategory } from '../models';
import { Authorization } from '../../Openid/models';

import Row from './Row';

const useStyles = makeStyles<Theme, { columns }, string>((theme) => ({
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
}: InputPropsWithoutCategory): JSX.Element => {
  const classes = useStyles({
    columns: fieldsTableConfiguration?.columns.length,
  });

  const { themeMode } = useAtomValue(userAtom);

  const { values, errors } = useFormikContext<FormikValues>();

  const tableValues = prop(fieldName, values) as Array<Authorization | null>;

  const fieldsTableError = prop(fieldName, errors) as string | undefined;

  return useMemoComponent({
    Component: (
      <div>
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
    memoProps: [tableValues, fieldsTableError, themeMode],
  });
};

export default FieldsTable;
