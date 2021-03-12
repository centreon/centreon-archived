import * as React from 'react';

import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { and, equals, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { DateTimePicker, MuiPickersUtilsProvider } from '@material-ui/pickers';
import { FormHelperText, makeStyles, Typography } from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';
import { dateTimeFormat, TextField } from '@centreon/ui';

import {
  labelEndDate,
  labelStartDate,
  labelStartDateIsSameOrAfterEndDate,
  labelTo,
} from '../../../translatedLabels';
import {
  CustomTimePeriod,
  CustomTimePeriodProperties,
} from '../../../Details/tabs/Graph/models';
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

interface AcceptDateProps {
  property: CustomTimePeriodProperties;
  date: Date;
}

interface Props {
  customTimePeriod: CustomTimePeriod;
  acceptDate: (props: AcceptDateProps) => void;
}

dayjs.extend(isSameOrAfter);

const useStyles = makeStyles((theme) => ({
  pickers: {
    display: 'grid',
    gridTemplateColumns: `minmax(${theme.spacing(18)}px, ${theme.spacing(
      20,
    )}px) min-content minmax(${theme.spacing(18)}px, ${theme.spacing(20)}px)`,
    columnGap: `${theme.spacing(0.5)}px`,
    alignItems: 'center',
  },
  error: {
    textAlign: 'center',
  },
}));

const CustomTimePeriodPickers = ({
  customTimePeriod,
  acceptDate,
}: Props): JSX.Element => {
  const [start, setStart] = React.useState<Date>(customTimePeriod.start);
  const [end, setEnd] = React.useState<Date>(customTimePeriod.end);
  const classes = useStyles();
  const { t } = useTranslation();
  const { locale } = useUserContext();
  const Adapter = useDateTimePickerAdapter();

  const isInvalidDate = ({ startDate, endDate }) =>
    dayjs(startDate).isSameOrAfter(dayjs(endDate), 'minute');

  const changeDate = (property: CustomTimePeriodProperties) => () => {
    const dateToAccept = equals(property, CustomTimePeriodProperties.start)
      ? start
      : end;

    if (
      or(
        dayjs(dateToAccept).isSame(dayjs(customTimePeriod[property])),
        isInvalidDate({ startDate: start, endDate: end }),
      )
    ) {
      return;
    }
    acceptDate({
      date: dateToAccept,
      property,
    });
  };

  React.useEffect(() => {
    if (
      and(
        dayjs(customTimePeriod.start).isSame(dayjs(start), 'minute'),
        dayjs(customTimePeriod.end).isSame(dayjs(end), 'minute'),
      )
    ) {
      return;
    }
    setStart(customTimePeriod.start);
    setEnd(customTimePeriod.end);
  }, [customTimePeriod.start, customTimePeriod.end]);

  const isError = isInvalidDate({ startDate: start, endDate: end });

  const commonPickersProps = {
    autoOk: true,
    error: undefined,
    InputProps: {
      disableUnderline: true,
    },
    format: dateTimeFormat,
  };

  const startDateInputProp = {
    TextFieldComponent: (textFieldProps) => (
      <TextField {...textFieldProps} ariaLabel={t(labelStartDate)} />
    ),
  };

  const endDateInputProp = {
    TextFieldComponent: (textFieldProps) => (
      <TextField {...textFieldProps} ariaLabel={t(labelEndDate)} />
    ),
  };

  return (
    <div>
      <div className={classes.pickers}>
        <MuiPickersUtilsProvider
          utils={Adapter}
          locale={locale.substring(0, 2)}
        >
          <DateTimePicker
            {...commonPickersProps}
            {...startDateInputProp}
            variant="inline"
            inputVariant="filled"
            value={start}
            onChange={(value) => setStart(new Date(value?.toDate() || 0))}
            onClose={changeDate(CustomTimePeriodProperties.start)}
            maxDate={customTimePeriod.end}
            size="small"
          />
          <Typography>{t(labelTo).toLowerCase()}</Typography>
          <DateTimePicker
            {...commonPickersProps}
            {...endDateInputProp}
            variant="inline"
            inputVariant="filled"
            value={end}
            onChange={(value) => setEnd(new Date(value?.toDate() || 0))}
            onClose={changeDate(CustomTimePeriodProperties.end)}
            minDate={customTimePeriod.start}
            size="small"
          />
        </MuiPickersUtilsProvider>
      </div>
      {isError && (
        <FormHelperText error className={classes.error}>
          {t(labelStartDateIsSameOrAfterEndDate)}
        </FormHelperText>
      )}
    </div>
  );
};

export default CustomTimePeriodPickers;
