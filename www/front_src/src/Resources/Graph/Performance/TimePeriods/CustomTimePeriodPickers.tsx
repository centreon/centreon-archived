import * as React from 'react';

import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { and, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { DateTimePicker, MuiPickersUtilsProvider } from '@material-ui/pickers';
import {
  FormHelperText,
  makeStyles,
  TextFieldProps,
  Typography,
} from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';
import { dateTimeFormat, TextField } from '@centreon/ui';

import {
  labelEndDate,
  labelStartDate,
  labelEndDateGreaterThanStartDate,
  labelTo,
} from '../../../translatedLabels';
import {
  CustomTimePeriod,
  CustomTimePeriodProperty,
} from '../../../Details/tabs/Graph/models';
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

interface AcceptDateProps {
  property: CustomTimePeriodProperty;
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

  const changeDate = ({ property, date }) => () => {
    const currentDate = customTimePeriod[property];

    if (
      or(
        dayjs(date).isSame(dayjs(currentDate)),
        isInvalidDate({ startDate: start, endDate: end }),
      )
    ) {
      return;
    }
    acceptDate({
      date,
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

  const error = isInvalidDate({ startDate: start, endDate: end });

  const commonPickersProps = {
    autoOk: true,
    error: undefined,
    InputProps: {
      disableUnderline: true,
    },
    format: dateTimeFormat,
  };

  const startDateInputProp = {
    TextFieldComponent: TextField as React.ComponentType<TextFieldProps>,
  };

  const endDateInputProp = {
    TextFieldComponent: TextField as React.ComponentType<TextFieldProps>,
  };

  return (
    <div>
      <div className={classes.pickers}>
        <MuiPickersUtilsProvider
          utils={Adapter}
          locale={locale.substring(0, 2)}
        >
          <div aria-label={t(labelStartDate)}>
            <DateTimePicker
              {...commonPickersProps}
              {...startDateInputProp}
              variant="inline"
              inputVariant="filled"
              value={start}
              onChange={(value) => setStart(new Date(value?.toDate() || 0))}
              onClose={changeDate({
                property: CustomTimePeriodProperty.start,
                date: start,
              })}
              maxDate={customTimePeriod.end}
              size="small"
            />
          </div>
          <Typography>{t(labelTo).toLowerCase()}</Typography>
          <div aria-label={t(labelEndDate)}>
            <DateTimePicker
              {...commonPickersProps}
              {...endDateInputProp}
              variant="inline"
              inputVariant="filled"
              value={end}
              onChange={(value) => setEnd(new Date(value?.toDate() || 0))}
              onClose={changeDate({
                property: CustomTimePeriodProperty.end,
                date: end,
              })}
              minDate={customTimePeriod.start}
              size="small"
            />
          </div>
        </MuiPickersUtilsProvider>
      </div>
      {error && (
        <FormHelperText error className={classes.error}>
          {t(labelEndDateGreaterThanStartDate)}
        </FormHelperText>
      )}
    </div>
  );
};

export default CustomTimePeriodPickers;
