import * as React from 'react';

import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { and, equals, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { DateTimePicker, MuiPickersUtilsProvider } from '@material-ui/pickers';
import { FormHelperText, makeStyles } from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';

import {
  labelEndDate,
  labelStartDate,
  labelStartDateIsSameOrAfterEndDate,
} from '../../../translatedLabels';
import {
  Timeframe,
  TimeframeProperties,
} from '../../../Details/tabs/Graph/models';
import useAdapter from '../../../useAdapter';

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
    gridTemplateColumns: `repeat(2, ${theme.spacing(14.5)}px)`,
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
  const classes = useStyles();
  const { t } = useTranslation();
  const { locale } = useUserContext();
  const Adapter = useAdapter();

  const isInvalidDate = ({ startDate, endDate }) =>
    dayjs(startDate).isSameOrAfter(dayjs(endDate), 'minute');

  const changeDate = (property: TimeframeProperties) => () => {
    const dateToAccept = equals(property, TimeframeProperties.start)
      ? start
      : end;

    if (
      or(
        dayjs(dateToAccept).isSame(dayjs(timeframe[property])),
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
            variant="inline"
            value={start}
            onChange={(value) => setStart(new Date(value?.toDate() || 0))}
            onClose={changeDate(TimeframeProperties.start)}
            label={t(labelStartDate)}
            maxDate={timeframe.end}
            size="small"
            inputProps={{
              'aria-label': t(labelStartDate),
            }}
          />
          <DateTimePicker
            {...commonPickersProps}
            variant="inline"
            value={end}
            onChange={(value) => setEnd(new Date(value?.toDate() || 0))}
            onClose={changeDate(TimeframeProperties.end)}
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
