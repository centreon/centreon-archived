import { FormikValues, useFormikContext } from 'formik';
import { not, prop, remove } from 'ramda';

import { Theme } from '@mui/material';
import { CreateCSSProperties, makeStyles } from '@mui/styles';
import DeleteIcon from '@mui/icons-material/Delete';

import { IconButton } from '@centreon/ui';

import { getInput } from '..';
import { InputPropsWithoutCategory } from '../models';

const useStyles = makeStyles<Theme, { columns }, string>((theme) => ({
  icon: {
    marginTop: theme.spacing(0.5),
  },
  inputsRow: ({ columns }): CreateCSSProperties => ({
    columnGap: theme.spacing(2),
    display: 'grid',
    gridTemplateColumns: `repeat(${columns}, 1fr) ${theme.spacing(6)}`,
  }),
}));

interface Props {
  columns?: Array<InputPropsWithoutCategory>;
  defaultRowValue?: object;
  getRequired: () => boolean;
  index: number;
  isLastElement: boolean;
  label: string;
  tableFieldName: string;
}

const Row = ({
  label,
  index,
  columns,
  tableFieldName,
  defaultRowValue,
  getRequired,
  isLastElement,
}: Props): JSX.Element => {
  const classes = useStyles({ columns: columns?.length });

  const { setFieldValue, values } = useFormikContext<FormikValues>();

  const tableValues = prop(tableFieldName, values);
  const rowValues = tableValues[index];

  const deleteRow = (): void => {
    setFieldValue(tableFieldName, remove(index, 1, tableValues));
  };

  const changeRow = ({ property, value }): void => {
    const currentRowValue = rowValues || defaultRowValue;

    setFieldValue(`${tableFieldName}.${index}`, {
      ...currentRowValue,
      [property]: value,
    });
  };

  return (
    <div className={classes.inputsRow} key={`${label}_${index}`}>
      {columns?.map((field): JSX.Element => {
        const Input = getInput(field.type);

        return (
          <Input
            {...field}
            additionalMemoProps={[rowValues]}
            change={({ value }): void =>
              changeRow({
                property: field.fieldName,
                value,
              })
            }
            fieldName={`${tableFieldName}.${index}.${field.fieldName}`}
            getRequired={getRequired}
            key={`${label}_${index}_${field.label}`}
          />
        );
      })}
      {not(isLastElement) && (
        <IconButton className={classes.icon} onClick={deleteRow}>
          <DeleteIcon />
        </IconButton>
      )}
    </div>
  );
};

export default Row;
