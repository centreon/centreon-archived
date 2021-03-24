import * as React from 'react';

import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { and, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { MuiPickersUtilsProvider } from '@material-ui/pickers';
import { FormHelperText, makeStyles, Typography } from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';
import { dateTimeFormat } from '@centreon/ui';

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

import CompactCustomTimePeriodPickers from './CompactCustomTimePeriodPickers';
import DateTimePickerInput from './DateTimePickerInput';

interface AcceptDateProps {
  property: CustomTimePeriodProperty;
  date: Date;
}

interface Props {
  customTimePeriod: CustomTimePeriod;
  acceptDate: (props: AcceptDateProps) => void;
  isMinimalWidth: boolean;
}

dayjs.extend(isSameOrAfter);

const useStyles = makeStyles((theme) => ({
  pickers: {
    display: 'grid',
    gridTemplateColumns: `minmax(${theme.spacing(15)}px, ${theme.spacing(
      17,
    )}px) min-content minmax(${theme.spacing(15)}px, ${theme.spacing(17)}px)`,
    columnGap: `${theme.spacing(0.5)}px`,
    alignItems: 'center',
  },
  error: {
    textAlign: 'center',
  },
  minimalPickers: {
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
    columnGap: `${theme.spacing(1)}px`,
    alignItems: 'center',
  },
  minimalFromTo: {
    display: 'grid',
    gridTemplateRows: 'repeat(2, min-content)',
    rowGap: `${theme.spacing(0.3)}px`,
  },
  pickerText: {
    lineHeight: '1.2',
    cursor: 'pointer',
  },
}));

const CustomTimePeriodPickers = ({
  customTimePeriod,
  acceptDate,
  isMinimalWidth,
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

  if (isMinimalWidth) {
    return (
      <CompactCustomTimePeriodPickers
        customTimePeriod={customTimePeriod}
        start={start}
        end={end}
        commonPickersProps={commonPickersProps}
        error={error}
        changeDate={changeDate}
        setStart={setStart}
        setEnd={setEnd}
      />
    );
  }

  return (
    <div>
      <div className={classes.pickers}>
        <MuiPickersUtilsProvider
          utils={Adapter}
          locale={locale.substring(0, 2)}
        >
          <div aria-label={t(labelStartDate)}>
            <DateTimePickerInput
              commonPickersProps={commonPickersProps}
              date={start}
              property={CustomTimePeriodProperty.start}
              maxDate={customTimePeriod.end}
              changeDate={changeDate}
              setDate={setStart}
            />
          </div>
          <Typography>{t(labelTo).toLowerCase()}</Typography>
          <div aria-label={t(labelEndDate)}>
            <DateTimePickerInput
              commonPickersProps={commonPickersProps}
              date={end}
              property={CustomTimePeriodProperty.end}
              minDate={customTimePeriod.start}
              changeDate={changeDate}
              setDate={setEnd}
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
