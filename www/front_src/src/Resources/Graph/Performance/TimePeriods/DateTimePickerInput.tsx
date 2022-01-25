import * as React from 'react';

import dayjs from 'dayjs';

import { DateTimePicker } from '@mui/lab';
import { TextFieldProps } from '@mui/material';

import { TextField } from '@centreon/ui';

import { CustomTimePeriodProperty } from '../../../Details/tabs/Graph/models';

interface Props {
  changeDate: (props) => () => void;
  date: Date;
  maxDate?: Date;
  minDate?: Date;
  property: CustomTimePeriodProperty;
  setDate: React.Dispatch<React.SetStateAction<Date>>;
}

const renderDateTimePickerTextField =
  (onClick) =>
  ({ inputRef, inputProps, InputProps }: TextFieldProps): JSX.Element => {
    return (
      <TextField
        disabled
        // eslint-disable-next-line react/no-unstable-nested-components
        EndAdornment={(): JSX.Element => <div>{InputProps?.endAdornment}</div>}
        inputProps={{
          ...inputProps,
          ref: inputRef,
          style: { padding: 8 },
        }}
        onClick={onClick}
      />
    );
  };

const DateTimePickerInput = ({
  date,
  maxDate,
  minDate,
  property,
  setDate,
  changeDate,
}: Props): JSX.Element => {
  const [isOpen, setIsOpen] = React.useState(false);

  const toggleIsOpen = (): void => {
    setIsOpen(!isOpen);
  };

  return (
    <DateTimePicker<dayjs.Dayjs>
      hideTabs
      PopperProps={{
        open: isOpen,
      }}
      maxDate={dayjs(maxDate)}
      minDate={dayjs(minDate)}
      open={isOpen}
      renderInput={renderDateTimePickerTextField(toggleIsOpen)}
      value={date}
      onChange={(value): void => {
        setDate(new Date(value?.toDate() || 0));
      }}
      onClose={changeDate({
        date,
        property,
      })}
    />
  );
};

export default DateTimePickerInput;
