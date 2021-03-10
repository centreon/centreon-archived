import * as React from 'react';

import DayjsUtils from '@date-io/dayjs';
import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { and, equals, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { DateTimePicker, MuiPickersUtilsProvider } from '@material-ui/pickers';
import { FormHelperText, makeStyles } from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';

import {
  labelCancel,
  labelEndDate,
  labelOk,
  labelStartDate,
  labelStartDateIsSameOrAfterEndDate,
} from '../../../translatedLabels';
import {
  Timeframe,
  TimeframeProperties,
} from '../../../Details/tabs/Graph/models';

interface AcceptDateProps {
  property: TimeframeProperties;
  date: Date;
}

interface Props {
  timeframe: Timeframe;
  acceptDate: (props: AcceptDateProps) => void;
}

dayjs.extend(isSameOrAfter);

const useStyles = makeStyles((theme) => ({
  pickers: {
    display: 'grid',
    gridTemplateColumns: `repeat(2, ${theme.spacing(16)}px)`,
    columnGap: `${theme.spacing(3)}px`,
  },
  error: {
    textAlign: 'center',
  },
}));

const CustomTimeframePickers = ({
  timeframe,
  acceptDate,
}: Props): JSX.Element => {
  const [start, setStart] = React.useState<Date>(timeframe.start);
  const [end, setEnd] = React.useState<Date>(timeframe.end);
  const { locale } = useUserContext();
  const classes = useStyles();
  const { t } = useTranslation();

  const isInvalidDate = ({ startDate, endDate }) =>
    dayjs(startDate).isSameOrAfter(dayjs(endDate), 'minute');

  const changeStartDate = (property: TimeframeProperties) => (value) => {
    setStart(value.toDate());
    if (
      or(
        isInvalidDate({ startDate: value.toDate(), endDate: end }),
        equals(value?.toDate().getTime(), start.getTime()),
      )
    ) {
      return;
    }
    acceptDate({ date: value.toDate(), property });
  };

  const changeEndDate = (property: TimeframeProperties) => (value) => {
    setEnd(value.toDate());
    if (
      or(
        isInvalidDate({ startDate: start, endDate: value.toDate() }),
        equals(value?.toDate().getTime(), end.getTime()),
      )
    ) {
      return;
    }
    acceptDate({ date: value.toDate(), property });
  };

  React.useEffect(() => {
    if (
      and(
        dayjs(timeframe.start).isSame(dayjs(start), 'minute'),
        dayjs(timeframe.end).isSame(dayjs(end), 'minute'),
      )
    ) {
      return;
    }
    setStart(timeframe.start);
    setEnd(timeframe.end);
  }, [timeframe.start, timeframe.end]);

  const isError = isInvalidDate({ startDate: start, endDate: end });

  const commonPickersProps = {
    autoOk: true,
    ampm: false,
    format: 'YYYY/MM/DD HH:mm',
    okLabel: t(labelOk),
    cancelLabel: t(labelCancel),
  };

  return (
    <div>
      <div className={classes.pickers}>
        <MuiPickersUtilsProvider
          utils={DayjsUtils}
          locale={locale.substring(0, 2)}
        >
          <DateTimePicker
            {...commonPickersProps}
            value={start}
            onChange={changeStartDate(TimeframeProperties.start)}
            label={t(labelStartDate)}
            maxDate={timeframe.end}
            size="small"
            inputProps={{
              'aria-label': t(labelStartDate),
            }}
          />
          <DateTimePicker
            {...commonPickersProps}
            value={end}
            onChange={changeEndDate(TimeframeProperties.end)}
            label={t(labelEndDate)}
            minDate={timeframe.start}
            size="small"
            inputProps={{
              'aria-label': t(labelEndDate),
            }}
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

export default CustomTimeframePickers;
