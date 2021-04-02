import * as React from 'react';

import { DateTimePicker } from '@material-ui/pickers';

import { TextField } from '@centreon/ui';

import { CustomTimePeriodProperty } from '../../../Details/tabs/Graph/models';

const DateTimeTextField = React.forwardRef(
  (props, ref: React.ForwardedRef<HTMLDivElement>): JSX.Element => (
    <TextField {...props} size="small" ref={ref} />
  ),
);

interface Props {
  commonPickersProps;
  date: Date;
  minDate?: Date;
  maxDate?: Date;
  property: CustomTimePeriodProperty;
  setDate: React.Dispatch<React.SetStateAction<Date>>;
  changeDate: (props) => () => void;
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
      variant="inline"
      inputVariant="filled"
      value={date}
      onChange={(value) => setDate(new Date(value?.toDate() || 0))}
      onClose={changeDate({
        property,
        date,
      })}
      maxDate={maxDate}
      minDate={minDate}
      size="small"
    />
  );
};

export default DateTimePickerInput;
