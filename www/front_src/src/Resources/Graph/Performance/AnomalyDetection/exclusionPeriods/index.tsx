/* eslint-disable hooks/sort */
import { useEffect, useState } from 'react';

import dayjs from 'dayjs';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';
import { equals, find, path, propEq } from 'ramda';
import { makeStyles } from 'tss-react/mui';

import AddIcon from '@mui/icons-material/Add';
import {
  Button,
  Divider,
  List,
  ListItem,
  ListItemText,
  Typography,
} from '@mui/material';

import { getData, useRequest } from '@centreon/ui';

import { centreonUi } from '../../../../../Header/helpers';
import { detailsAtom } from '../../../../Details/detailsAtoms';
import { GraphData, Line, TimeValue } from '../../models';
import PopoverCustomTimePeriodPickers from '../../TimePeriods/PopoverCustomTimePeriodPickers';
import {
  customTimePeriodAtom,
  graphQueryParametersDerivedAtom,
} from '../../TimePeriods/timePeriodAtoms';
import { getLineData, getTimeSeries } from '../../timeSeries';
import { thresholdsAnomalyDetectionDataAtom } from '../anomalyDetectionAtom';

import AnomalyDetectionCommentExclusionPeriod from './AnomalyDetectionCommentExclusionPeriods';
import AnomalyDetectionFooterExclusionPeriods from './AnomalyDetectionFooterExclusionPeriods';
import AnomalyDetectionTitleExclusionPeriods from './AnomalyDetectionTitleExclusionPeriods';

const useStyles = makeStyles()((theme) => ({
  body: {
    display: 'flex',
    justifyContent: 'center',
    marginTop: theme.spacing(5),
  },
  container: {
    display: 'flex',
    padding: theme.spacing(2),
  },
  divider: {
    margin: theme.spacing(0, 2),
  },
  error: {
    textAlign: 'left',
  },

  excludedPeriods: {
    display: 'flex',
    flexDirection: 'column',
    width: '50%',
  },
  exclusionButton: {
    width: theme.spacing(22.5),
  },
  list: {
    backgroundColor: theme.palette.action.disabledBackground,
    maxHeight: theme.spacing(150 / 8),
    minHeight: theme.spacing(150 / 8),
    overflow: 'auto',
  },
  paper: {
    '& .MuiPopover-paper': {
      padding: theme.spacing(2),
      // width: 350,
      // width: '40%',
    },
  },
  picker: {
    flexDirection: 'row',
    padding: 0,
  },
  subContainer: {
    display: 'flex',
    flexDirection: 'column',
  },
  title: {
    color: theme.palette.text.disabled,
  },
}));

const AnomalyDetectionExclusionPeriod = ({ display }: any): JSX.Element => {
  const { classes } = useStyles();

  const [open, setOpen] = useState(false);
  const [endDate, setEndDate] = useState(undefined);
  const [startDate, setStartDate] = useState(undefined);
  const [timeSeries, setTimeSeries] = useState<Array<TimeValue>>([]);
  const [lineData, setLineData] = useState<Array<Line>>();
  const [isErrorDatePicker, setIsErrorDatePicker] = useState(false);
  const [enabledExclusionButton, setEnabledExclusionButton] = useState(false);
  const [isExclusionPeriodChecked, setIsExclusionPeriodChecked] =
    useState(false);
  const [disabledPickerEndInput, setDisabledPickerEndInput] = useState(false);
  const [disabledPickerStartInput, setDisabledPickerStartInput] =
    useState(false);

  const dateExisted = startDate && endDate;
  const { sendRequest: sendGetGraphDataRequest } = useRequest<GraphData>({
    request: getData,
  });
  const [viewStartPicker, setViewStartPicker] = useState<string | null>(null);
  const [viewEndPicker, setViewEndPicker] = useState<string | null>(null);

  const [thresholdsAnomalyDetectionData, setThresholdAnomalyDetectionData] =
    useAtom(thresholdsAnomalyDetectionDataAtom);
  const customTimePeriod = useAtomValue(customTimePeriodAtom);
  const getGraphQueryParameters = useAtomValue(graphQueryParametersDerivedAtom);
  const details = useAtomValue(detailsAtom);

  const isInvalidDate = ({ start, end }): boolean =>
    dayjs(start).isSameOrAfter(dayjs(end), 'minute');

  const [exclusionTimePeriods, setExclusionTimePeriods] =
    useState(customTimePeriod);
  const { data } = thresholdsAnomalyDetectionData.exclusionPeriodsThreshold;

  const endpoint = path(['links', 'endpoints', 'performance_graph'], details);
  const { toDate } = centreonUi.useLocaleDateTimeFormat();

  const maxDateEndInputPicker = dayjs(exclusionTimePeriods?.end).add(1, 'day');

  const [pickerStartWithoutInitialValue, setPickerStartWithoutInitialValue] =
    useState(true);
  const [pickerEndWithoutInitialValue, setPickerEndWithoutInitialValue] =
    useState(true);

  const listExcludedDates =
    thresholdsAnomalyDetectionData?.exclusionPeriodsThreshold
      ?.selectedDateToDelete;

  const exclude = (): void => {
    setOpen(true);
    setExclusionTimePeriods(customTimePeriod);
    setEndDate(undefined);
    setStartDate(undefined);
  };

  const anchorPosition = {
    left: window.innerWidth / 2,
    top: window.innerHeight / 2,
  };

  const close = (): void => {
    setEndDate(undefined);
    setStartDate(undefined);
    setOpen(false);
  };

  const changeDate = ({ property, date }): void => {
    if (equals(property, 'end')) {
      setEndDate(date);

      return;
    }
    setStartDate(date);
  };

  const graphEndpoint = (): string | undefined => {
    const graphQuerParameters = getGraphQueryParameters({
      endDate,
      startDate,
    });

    return `${endpoint}${graphQuerParameters}`;
  };

  const addCurrentData = (): void => {
    console.log('add current');
    const filteredData = data.map((item) => {
      if (item.isConfirmed === false) {
        return { isConfirmed: false, lines: lineData, timeSeries };
      }

      return item;
    });

    console.log({ filteredData });

    setThresholdAnomalyDetectionData({
      ...thresholdsAnomalyDetectionData,
      exclusionPeriodsThreshold: {
        ...thresholdsAnomalyDetectionData.exclusionPeriodsThreshold,
        data: [...filteredData],
      },
    });
  };

  console.log({ data });

  const initializeData = (): void => {
    setStartDate(undefined);
    setEndDate(undefined);
    setPickerEndWithoutInitialValue(true);
    setPickerStartWithoutInitialValue(true);
    setIsExclusionPeriodChecked(false);
    setDisabledPickerEndInput(false);
    setDisabledPickerStartInput(false);
  };

  const confirmExcluderPeriods = (): void => {
    const excludedData = data.map((item) =>
      item.isConfirmed === false ? { ...item, isConfirmed: true } : item,
    );

    setThresholdAnomalyDetectionData({
      ...thresholdsAnomalyDetectionData,
      exclusionPeriodsThreshold: {
        ...thresholdsAnomalyDetectionData.exclusionPeriodsThreshold,
        data: [
          ...excludedData,
          { isConfirmed: false, lines: [], timeSeries: [] },
        ],
        selectedDateToDelete: [
          ...thresholdsAnomalyDetectionData.exclusionPeriodsThreshold
            .selectedDateToDelete,
          { end: endDate, start: startDate },
        ],
      },
    });
    setOpen(false);
    initializeData();
  };

  const deleteCurrentData = (): void => {
    const filteredData = data.map((item) => {
      if (item.isConfirmed === false) {
        return { isConfirmed: false, lines: [], timeSeries: [] };
      }

      return item;
    });

    setThresholdAnomalyDetectionData({
      ...thresholdsAnomalyDetectionData,
      exclusionPeriodsThreshold: {
        ...thresholdsAnomalyDetectionData.exclusionPeriodsThreshold,
        data: [...filteredData],
      },
    });
  };

  const cancelExclusionPeriod = (): void => {
    deleteCurrentData();
    setOpen(false);
    initializeData();
  };

  const viewChangeStartPicker = (view: string): void => {
    setViewStartPicker(view);
  };
  const viewChangeEndPicker = (view: string): void => {
    setViewEndPicker(view);
  };

  interface CallbackForSelectMinutes {
    date: Date;
    property: any;
  }

  const callbackForSelectMinutes = ({
    property,
    date,
  }: CallbackForSelectMinutes): void => {
    if (
      (!equals(viewStartPicker, 'minutes') && equals(property, 'start')) ||
      (!equals(viewEndPicker, 'minutes') && equals(property, 'end'))
    ) {
      return;
    }
    changeDate({
      date,
      property,
    });
    if (equals(viewStartPicker, 'minutes') && equals(property, 'start')) {
      setViewStartPicker(null);
    }
    if (equals(viewEndPicker, 'minutes') && equals(property, 'end')) {
      setViewEndPicker(null);
    }
  };

  const getIsError = (value: boolean): void => {
    setIsErrorDatePicker(value);
    if (value) {
      setEndDate(undefined);
    }
  };

  const handleCheckedExclusionPeriod = ({ target }): void => {
    setIsExclusionPeriodChecked(target.checked);
    if (!target.checked) {
      setStartDate(undefined);
      setEndDate(undefined);
      setPickerStartWithoutInitialValue(true);
      setPickerEndWithoutInitialValue(true);
      setDisabledPickerEndInput(false);
      setDisabledPickerStartInput(false);
      deleteCurrentData();

      return;
    }
    setPickerStartWithoutInitialValue(false);
    setPickerEndWithoutInitialValue(false);
    setDisabledPickerEndInput(true);
    setDisabledPickerStartInput(true);
    setStartDate(exclusionTimePeriods.start);
    setEndDate(exclusionTimePeriods.end);
  };

  useEffect(() => {
    if (!startDate || !endDate) {
      return;
    }
    console.log('call');

    const api = graphEndpoint();
    getGraphData(api);
  }, [startDate, endDate]);

  const getGraphData = (api): any => {
    sendGetGraphDataRequest({
      endpoint: api,
    })
      .then((graphData) => {
        console.log({ graphData });
        setTimeSeries(getTimeSeries(graphData));
        const newLineData = getLineData(graphData);

        if (lineData) {
          setLineData(
            newLineData.map((line) => ({
              ...line,
              display:
                find(propEq('name', line.name), lineData)?.display ?? true,
            })),
          );

          return;
        }

        setLineData(newLineData);
      })
      .catch((err) => console.log('err', err));
  };

  useEffect(() => {
    if (!lineData || !timeSeries) {
      return;
    }
    addCurrentData();
  }, [timeSeries, lineData]);

  useEffect(() => {
    setEnabledExclusionButton(
      isInvalidDate({
        end: customTimePeriod?.end,
        start: customTimePeriod?.start,
      }),
    );
  }, [customTimePeriod]);

  return (
    <div className={classes.container}>
      <div className={classes.subContainer}>
        <Typography variant="h6">Exclusion of periods</Typography>
        <Typography variant="caption">
          Attention, the excluded of periods will be applied immediately.
        </Typography>
        <div className={classes.body}>
          <Button
            className={classes.exclusionButton}
            data-testid="exclude"
            disabled={enabledExclusionButton}
            size="small"
            startIcon={<AddIcon />}
            variant="contained"
            onClick={exclude}
          >
            Exclude a period
          </Button>
        </div>
      </div>
      <Divider flexItem className={classes.divider} orientation="vertical" />
      <div className={classes.excludedPeriods}>
        <Typography className={classes.title} variant="h6">
          Excluded periods
        </Typography>
        <List className={classes.list}>
          {listExcludedDates.map((item, index) => (
            <ListItem key={index}>
              <ListItemText
                primary={`From ${toDate(item?.start)} To ${toDate(item?.end)}`}
              />
            </ListItem>
          ))}
        </List>
      </div>
      <PopoverCustomTimePeriodPickers
        acceptDate={changeDate}
        anchorReference="anchorPosition"
        classNameError={classes.error}
        classNamePaper={classes.paper}
        classNamePicker={classes.picker}
        customTimePeriod={exclusionTimePeriods}
        disabledPickerEndInput={disabledPickerEndInput}
        disabledPickerStartInput={disabledPickerStartInput}
        getIsErrorDatePicker={getIsError}
        maxDatePickerEndInput={maxDateEndInputPicker}
        minDatePickerStartInput={exclusionTimePeriods?.start}
        open={open}
        pickerEndWithoutInitialValue={pickerStartWithoutInitialValue}
        pickerStartWithoutInitialValue={pickerEndWithoutInitialValue}
        reference={{ anchorPosition }}
        renderBody={
          <AnomalyDetectionCommentExclusionPeriod
            isExclusionPeriodChecked={isExclusionPeriodChecked}
            onChangeCheckedExclusionPeriod={handleCheckedExclusionPeriod}
          />
        }
        renderFooter={
          <AnomalyDetectionFooterExclusionPeriods
            cancelExclusionPeriod={cancelExclusionPeriod}
            confirmExcluderPeriods={confirmExcluderPeriods}
            dateExisted={dateExisted}
            isError={isErrorDatePicker}
            setOpen={setOpen}
          />
        }
        renderTitle={<AnomalyDetectionTitleExclusionPeriods />}
        setPickerEndWithoutInitialValue={setPickerStartWithoutInitialValue}
        setPickerStartWithoutInitialValue={setPickerEndWithoutInitialValue}
        viewChangeEndPicker={viewChangeEndPicker}
        viewChangeStartPicker={viewChangeStartPicker}
        onClose={close}
      />
    </div>
  );
};

export default AnomalyDetectionExclusionPeriod;
