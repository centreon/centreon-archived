import { useState, useEffect } from 'react';

import { useAtomValue } from 'jotai/utils';
import { makeStyles } from 'tss-react/mui';
import { equals, path, prop, propEq, reject, sortBy } from 'ramda';

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

import {
  detailsAtom,
  selectedResourcesDetailsAtom,
} from '../../../../Details/detailsAtoms';
import PopoverCustomTimePeriodPickers from '../../TimePeriods/PopoverCustomTimePeriodPickers';
import {
  customTimePeriodAtom,
  graphQueryParametersDerivedAtom,
  selectedTimePeriodAtom,
} from '../../TimePeriods/timePeriodAtoms';
import { GraphData, Line, TimeValue } from '../../models';
import { getTimeSeries } from '../../timeSeries';

import AnomalyDetectionCommentExclusionPeriod from './AnomalyDetectionCommentExclusionPeriods';
import AnomalyDetectionTitleExclusionPeriods from './AnomalyDetectionTitleExclusionPeriods';
import AnomalyDetectionFooterExclusionPeriods from './AnomalyDetectionFooterExclusionPeriods';

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

const AnomalyDetectionExclusionPeriod = ({ data }: any): JSX.Element => {
  const { classes } = useStyles();

  const [open, setOpen] = useState(false);
  const [endDate, setEndDate] = useState(undefined);
  const [startDate, setStartDate] = useState(undefined);
  const [newEndpoint, setNewEndPoint] = useState(undefined);
  const [lines, setLines] = useState(null);
  const [timeSeries, setTimeSeries] = useState<Array<TimeValue>>([]);
  const [lineData, setLineData] = useState<Array<Line>>();

  const {
    sendRequest: sendGetGraphDataRequest,
    sending: sendingGetGraphDataRequest,
  } = useRequest<GraphData>({
    request: getData,
  });

  const customTimePeriod = useAtomValue(customTimePeriodAtom);
  const selectedTimePeriod = useAtomValue(selectedTimePeriodAtom);
  const getGraphQueryParameters = useAtomValue(graphQueryParametersDerivedAtom);
  const details = useAtomValue(detailsAtom);
  const exclusionTimePeriods = { ...customTimePeriod };

  const endpoint = path(['links', 'endpoints', 'performance_graph'], details);

  const exclude = (): void => {
    setOpen(true);
  };

  const anchorPosition = {
    left: window.innerWidth / 2,
    top: window.innerHeight / 3,
  };

  const close = (): void => {
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
      // timePeriod: null,
    });

    return `${endpoint}${graphQuerParameters}`;
  };

  const sortedLines = sortBy(prop('name'), lineData);

  const displayedLines = reject(propEq('display', false), sortedLines);

  useEffect(() => {
    if (!startDate || !endDate) {
      return;
    }
    setNewEndPoint(graphEndpoint() as any);
  }, [startDate, endDate]);

  useEffect(() => {
    if (!newEndpoint) {
      return;
    }
    console.log({ newEndpoint });

    sendGetGraphDataRequest({
      endpoint: newEndpoint,
    })
      .then((graphData) => {
        console.log({ endpoint });
        setTimeSeries(getTimeSeries(graphData));
        // const newLineData = getLineData(graphData);

        // if (lineData) {
        //   setLineData(
        //     newLineData.map((line) => ({
        //       ...line,
        //       display:
        //         find(propEq('name', line.name), lineData)?.display ?? true,
        //     })),
        //   );

        //   return;
        // }

        // setLineData(newLineData);
      })
      .catch(() => undefined);
  }, [newEndpoint]);

  // lines timeseries base

  // useEffect(() => {
  //   if (isNil(endpoint)) {
  //     return;
  //   }

  //   sendGetGraphDataRequest({
  //     endpoint,
  //   })
  //     .then((graphData) => {
  //       console.log({ endpoint });
  //       setTimeSeries(getTimeSeries(graphData));
  //       setBase(graphData.global.base);
  //       setTitle(graphData.global.title);
  //       const newLineData = getLineData(graphData);

  //       if (lineData) {
  //         setLineData(
  //           newLineData.map((line) => ({
  //             ...line,
  //             display:
  //               find(propEq('name', line.name), lineData)?.display ?? true,
  //           })),
  //         );

  //         return;
  //       }

  //       setLineData(newLineData);
  //     })
  //     .catch(() => undefined);
  // }, [endpoint]);

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
          <ListItem>
            <ListItemText primary="test" />
          </ListItem>
        </List>
      </div>
      <PopoverCustomTimePeriodPickers
        acceptDate={changeDate}
        anchorReference="anchorPosition"
        classNamePaper={classes.paper}
        classNamePicker={classes.picker}
        customTimePeriod={exclusionTimePeriods}
        open={open}
        reference={{ anchorPosition }}
        renderBody={<AnomalyDetectionCommentExclusionPeriod />}
        renderFooter={
          <AnomalyDetectionFooterExclusionPeriods setOpen={setOpen} />
        }
        renderTitle={<AnomalyDetectionTitleExclusionPeriods />}
        onClose={close}
      />
    </div>
  );
};

export default AnomalyDetectionExclusionPeriod;
