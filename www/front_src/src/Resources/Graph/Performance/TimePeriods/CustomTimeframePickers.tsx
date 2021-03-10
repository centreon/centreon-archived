import * as React from 'react';

import DayjsUtils from '@date-io/dayjs';
import dayjs from 'dayjs';
import { equals, or } from 'ramda';

import { DateTimePicker, MuiPickersUtilsProvider } from '@material-ui/pickers';
import { MaterialUiPickersDate } from '@material-ui/pickers/typings/date';
import { FormHelperText, makeStyles } from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';

import { labelEndDate, labelStartDate } from '../../../translatedLabels';
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

  const changeStartDate = (value: MaterialUiPickersDate) => {
    setStart(value?.toDate() || new Date());
  };

  const changeEndDate = (value: MaterialUiPickersDate) => {
    setEnd(value?.toDate() || new Date());
  };

  const startIsAfterEnd = dayjs(start).isAfter(dayjs(end));

  const closeDateTimePicker = ({ date, property }: AcceptDateProps) => () => {
    if (
      or(startIsAfterEnd, equals(date.getTime(), timeframe[property].getTime()))
    ) {
      return;
    }

    acceptDate({ date, property });
  };

  const commonPickersProps = {
    autoOk: true,
    ampm: false,
    format: 'YYYY/MM/DD HH:mm',
  };

  React.useEffect(() => {
    setStart(timeframe.start);
    setEnd(timeframe.end);
  }, [timeframe.start, timeframe.end]);

  return (
    <div>
      <div className={classes.pickers}>
        <MuiPickersUtilsProvider
          utils={DayjsUtils}
          locale={locale.substring(0, 2)}
        >
          <DateTimePicker
            {...commonPickersProps}
            variant="inline"
            value={start}
            onChange={changeStartDate}
            onClose={closeDateTimePicker({
              property: TimeframeProperties.start,
              date: start,
            })}
            label={labelStartDate}
            maxDate={end}
            size="small"
          />
          <DateTimePicker
            {...commonPickersProps}
            variant="inline"
            value={end}
            onChange={changeEndDate}
            onClose={closeDateTimePicker({
              property: TimeframeProperties.end,
              date: end,
            })}
            label={labelEndDate}
            minDate={start}
            size="small"
          />
        </MuiPickersUtilsProvider>
      </div>
      {startIsAfterEnd && (
        <FormHelperText error className={classes.error}>
          Start date is after end date
        </FormHelperText>
      )}
    </div>
  );
};

export default CustomTimeframePickers;
