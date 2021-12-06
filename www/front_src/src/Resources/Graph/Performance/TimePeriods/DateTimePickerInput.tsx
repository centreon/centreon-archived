import * as React from 'react';

import { DateTimePicker } from '@material-ui/pickers';

import { TextField } from '@centreon/ui';

import { CustomTimePeriodProperty } from '../../../Details/tabs/Graph/models';

const DateTimeTextField = React.forwardRef(
  (props, ref: React.ForwardedRef<HTMLDivElement>): JSX.Element => (
    <TextField {...props} ref={ref} size="small" />
  ),
);

interface Props {
  changeDate: (props) => () => void;
  commonPickersProps;
  date: Date;
  maxDate?: Date;
  minDate?: Date;
  property: CustomTimePeriodProperty;
  setDate: React.Dispatch<React.SetStateAction<Date>>;
}

const DateTimePickerInput = ({
  commonPickersProps,
  date,
  minDate,
  maxDate,
  property,
  setDate,
  changeDate,
}: Props): JSX.Element => {
  const inputProp = {
    TextFieldComponent: DateTimeTextField,
  };

  return (
    <DateTimePicker
      {...commonPickersProps}
      {...inputProp}
      hideTabs
      inputVariant="filled"
      maxDate={maxDate}
      minDate={minDate}
      size="small"
      value={date}
      variant="inline"
      onChange={(value): void => setDate(new Date(value?.toDate() || 0))}
      onClose={changeDate({
        date,
        property,
      })}
    />
  );
};

export default DateTimePickerInput;
