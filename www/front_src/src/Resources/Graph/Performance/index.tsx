import * as React from 'react';

import { ParentSize } from '@visx/visx';
import {
  map,
  prop,
  propEq,
  find,
  reject,
  sortBy,
  isEmpty,
  isNil,
  head,
  equals,
  pipe,
  not,
  add,
  negate,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Typography, Theme } from '@material-ui/core';
import SaveAsImageIcon from '@material-ui/icons/SaveAlt';

import {
  useRequest,
  getData,
  timeFormat,
  ContentWithCircularLoading,
  IconButton,
} from '@centreon/ui';

import { TimelineEvent } from '../../Details/tabs/Timeline/models';
import { Resource } from '../../models';
import { ResourceDetails } from '../../Details/models';
import { CommentParameters } from '../../Actions/api';
import {
  labelExportToPng,
  labelNoDataForThisPeriod,
} from '../../translatedLabels';
import {
  CustomTimePeriod,
  CustomTimePeriodProperty,
} from '../../Details/tabs/Graph/models';

import Graph from './Graph';
import Legend from './Legend';
import LoadingSkeleton from './LoadingSkeleton';
import {
  GraphData,
  TimeValue,
  Line as LineModel,
  AdjustTimePeriodProps,
} from './models';
import { getTimeSeries, getLineData } from './timeSeries';
import useMetricsValue, { MetricsValueContext } from './Graph/useMetricsValue';
import { TimeShiftDirection } from './Graph/TimeShiftZones';
import exportToPng from './ExportableGraphWithTimeline/exportToPng';

interface Props {
  endpoint?: string;
  xAxisTickFormat?: string;
  graphHeight: number;
  toggableLegend?: boolean;
  resource: Resource | ResourceDetails;
  timeline?: Array<TimelineEvent>;
  onAddComment?: (commentParameters: CommentParameters) => void;
  tooltipPosition?: [number, number];
  onTooltipDisplay?: (position?: [number, number]) => void;
  adjustTimePeriod?: (props: AdjustTimePeriodProps) => void;
  customTimePeriod?: CustomTimePeriod;
  resourceDetailsUpdated?: boolean;
  displayEventAnnotations?: boolean;
  displayTooltipValues?: boolean;
  isInTooltip?: boolean;
}

interface MakeStylesProps extends Pick<Props, 'graphHeight' | 'isInTooltip'> {
  canAdjustTimePeriod: boolean;
}

const useStyles = makeStyles<Theme, MakeStylesProps>((theme) => ({
  container: {
    display: 'grid',
    flexDirection: 'column',
    gridTemplateRows: ({ graphHeight, isInTooltip }): string =>
      `${not(isInTooltip) ? 'auto' : ''} ${graphHeight}px auto`,
    gridGap: theme.spacing(1),
    height: '100%',
    justifyItems: 'center',
    width: 'auto',
  },
  noDataContainer: {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    height: '100%',
  },
  legend: {
    display: 'flex',
    flexWrap: 'wrap',
    justifyContent: 'center',
    alignItems: 'center',
    width: '100%',
  },
  graphTranslation: {
    display: 'grid',
    gridTemplateColumns: ({ canAdjustTimePeriod }) =>
      canAdjustTimePeriod ? 'min-content auto min-content' : 'auto',
    columnGap: `${theme.spacing(1)}px`,
    width: '90%',
    justifyContent: ({ canAdjustTimePeriod }) =>
      canAdjustTimePeriod ? 'space-between' : 'center',
    margin: theme.spacing(0, 1),
  },
  loadingContainer: {
    width: theme.spacing(2),
    height: theme.spacing(2),
  },
  graphHeader: {
    width: '100%',
    display: 'grid',
    gridTemplateColumns: '0.1fr 1fr 0.1fr',
    justifyItems: 'center',
  },
  exportToPngButton: {
    justifySelf: 'end',
  },
}));

const shiftRatio = 2;

const PerformanceGraph = ({
  endpoint,
  graphHeight,
  xAxisTickFormat = timeFormat,
  toggableLegend = false,
  timeline,
  tooltipPosition,
  onTooltipDisplay,
  resource,
  onAddComment,
  adjustTimePeriod,
  customTimePeriod,
  resourceDetailsUpdated = true,
  displayEventAnnotations = false,
  displayTooltipValues = false,
  isInTooltip = false,
}: Props): JSX.Element | null => {
  const classes = useStyles({
    graphHeight,
    canAdjustTimePeriod: not(isNil(adjustTimePeriod)),
    isInTooltip,
  });
  const { t } = useTranslation();

  const [timeSeries, setTimeSeries] = React.useState<Array<TimeValue>>([]);
  const [lineData, setLineData] = React.useState<Array<LineModel>>();
  const [title, setTitle] = React.useState<string>();
  const [base, setBase] = React.useState<number>();
  const [exporting, setExporting] = React.useState<boolean>(false);
  const performanceGraphRef = React.useRef<HTMLDivElement>();

  const {
    sendRequest: sendGetGraphDataRequest,
    sending: sendingGetGraphDataRequest,
  } = useRequest<GraphData>({
    request: getData,
  });
  const metricsValueProps = useMetricsValue();

  React.useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    sendGetGraphDataRequest(endpoint).then((graphData) => {
      setTimeSeries(getTimeSeries(graphData));
      setBase(graphData.global.base);
      setTitle(graphData.global.title);
      const newLineData = getLineData(graphData);
      if (lineData) {
        setLineData(
          newLineData.map((line) => ({
            ...line,
            display: find(propEq('name', line.name), lineData)?.display ?? true,
          })),
        );
        return;
      }
      setLineData(newLineData);
    });
  }, [endpoint]);

  if (isNil(lineData) || isNil(timeline) || isNil(endpoint)) {
    return (
      <LoadingSkeleton
        graphHeight={graphHeight}
        displayTitleSkeleton={not(isInTooltip)}
      />
    );
  }

  if (isEmpty(timeSeries) || isEmpty(lineData)) {
    return (
      <div className={classes.noDataContainer}>
        <Typography align="center" variant="body1">
          {t(labelNoDataForThisPeriod)}
        </Typography>
      </div>
    );
  }

  const sortedLines = sortBy(prop('name'), lineData);
  const displayedLines = reject(propEq('display', false), sortedLines);

  const getLineByMetric = (metric): LineModel => {
    return find(propEq('metric', metric), lineData) as LineModel;
  };

  const toggleMetricLine = (metric): void => {
    const line = getLineByMetric(metric);

    setLineData([
      ...reject(propEq('metric', metric), lineData),
      { ...line, display: !line.display },
    ]);
  };

  const highlightLine = (metric): void => {
    const fadedLines = map((line) => ({ ...line, highlight: false }), lineData);

    setLineData([
      ...reject(propEq('metric', metric), fadedLines),
      { ...getLineByMetric(metric), highlight: true },
    ]);
  };

  const clearHighlight = (): void => {
    setLineData(map((line) => ({ ...line, highlight: undefined }), lineData));
  };

  const selectMetricLine = (metric: string): void => {
    const metricLine = getLineByMetric(metric);

    const isLineDisplayed = pipe(head, equals(metricLine))(displayedLines);
    const isOnlyLineDisplayed = displayedLines.length === 1 && isLineDisplayed;

    if (isOnlyLineDisplayed || isEmpty(displayedLines)) {
      setLineData(
        map(
          (line) => ({
            ...line,
            display: true,
          }),
          lineData,
        ),
      );

      return;
    }

    setLineData(
      map(
        (line) => ({
          ...line,
          display: equals(line, metricLine),
        }),
        lineData,
      ),
    );
  };

  const getShiftedDate = ({ property, direction, timePeriod }): Date => {
    const adjustTimePeriodProps =
      (timePeriod.end.getTime() - timePeriod.start.getTime()) / shiftRatio;

    return new Date(
      add(
        prop(property, timePeriod).getTime(),
        equals(direction, TimeShiftDirection.backward)
          ? negate(adjustTimePeriodProps)
          : adjustTimePeriodProps,
      ),
    );
  };

  const shiftTime = (direction: TimeShiftDirection) => {
    if (isNil(customTimePeriod)) {
      return;
    }

    adjustTimePeriod?.({
      start: getShiftedDate({
        property: CustomTimePeriodProperty.start,
        direction,
        timePeriod: customTimePeriod,
      }),
      end: getShiftedDate({
        property: CustomTimePeriodProperty.end,
        direction,
        timePeriod: customTimePeriod,
      }),
    });
  };

  const convertToPng = (): void => {
    setExporting(true);
    exportToPng({
      element: performanceGraphRef.current as HTMLElement,
      title: `${resource?.name}-performance`,
    }).finally(() => {
      setExporting(false);
    });
  };

  return (
    <MetricsValueContext.Provider value={metricsValueProps}>
      <div
        className={classes.container}
        ref={performanceGraphRef as React.RefObject<HTMLDivElement>}
      >
        {not(isInTooltip) && (
          <div className={classes.graphHeader}>
            <div />
            <Typography variant="body1" color="textPrimary">
              {title}
            </Typography>
            <div className={classes.exportToPngButton}>
              <ContentWithCircularLoading
                loading={exporting}
                loadingIndicatorSize={16}
                alignCenter={false}
              >
                <IconButton
                  disabled={isNil(timeline)}
                  title={t(labelExportToPng)}
                  onClick={convertToPng}
                >
                  <SaveAsImageIcon style={{ fontSize: 18 }} />
                </IconButton>
              </ContentWithCircularLoading>
            </div>
          </div>
        )}

        <ParentSize>
          {({ width, height }): JSX.Element => (
            <Graph
              width={width}
              height={height}
              timeSeries={timeSeries}
              lines={displayedLines}
              base={base as number}
              xAxisTickFormat={xAxisTickFormat}
              timeline={timeline}
              onTooltipDisplay={onTooltipDisplay}
              tooltipPosition={tooltipPosition}
              resource={resource}
              onAddComment={onAddComment}
              applyZoom={adjustTimePeriod}
              shiftTime={shiftTime}
              loading={
                not(resourceDetailsUpdated) && sendingGetGraphDataRequest
              }
              canAdjustTimePeriod={not(isNil(adjustTimePeriod))}
              displayEventAnnotations={displayEventAnnotations}
              displayTooltipValues={displayTooltipValues}
            />
          )}
        </ParentSize>
        <div className={classes.legend}>
          <Legend
            lines={sortedLines}
            onToggle={toggleMetricLine}
            onSelect={selectMetricLine}
            toggable={toggableLegend}
            onHighlight={highlightLine}
            onClearHighlight={clearHighlight}
            base={base as number}
          />
        </div>
      </div>
    </MetricsValueContext.Provider>
  );
};

export default PerformanceGraph;
