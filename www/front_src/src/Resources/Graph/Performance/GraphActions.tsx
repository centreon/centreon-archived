import { MouseEvent, MutableRefObject, useState } from 'react';

import { isNil, equals } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';
import { useNavigate } from 'react-router-dom';

import { Divider, Menu, MenuItem, useTheme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import SaveAsImageIcon from '@mui/icons-material/SaveAlt';
import LaunchIcon from '@mui/icons-material/Launch';
import WrenchIcon from '@mui/icons-material/Build';

import {
  ContentWithCircularLoading,
  useLocaleDateTimeFormat,
  IconButton,
} from '@centreon/ui';

import {
  labelExport,
  labelAsDisplayed,
  labelMediumSize,
  labelPerformancePage,
  labelSmallSize,
  labelPerformanceGraphAD,
  labelCSV,
} from '../../translatedLabels';
import { CustomTimePeriod } from '../../Details/tabs/Graph/models';
import { TimelineEvent } from '../../Details/tabs/Timeline/models';
import memoizeComponent from '../../memoizedComponent';
import { ResourceType } from '../../models';
import { detailsAtom } from '../../Details/detailsAtoms';

import exportToPng from './ExportableGraphWithTimeline/exportToPng';
import {
  getDatesDerivedAtom,
  selectedTimePeriodAtom,
} from './TimePeriods/timePeriodAtoms';

interface Props {
  customTimePeriod?: CustomTimePeriod;
  getIsModalOpened: (value: boolean) => void;
  performanceGraphRef: MutableRefObject<HTMLDivElement | null>;
  resourceName: string;
  resourceParentName?: string;
  resourceType?: string;
  timeline?: Array<TimelineEvent>;
}

const useStyles = makeStyles((theme) => ({
  buttonGroup: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'inline',
    flexDirection: 'row',
  },
}));

const GraphActions = ({
  customTimePeriod,
  resourceParentName,
  resourceName,
  resourceType,
  timeline,
  performanceGraphRef,
  getIsModalOpened,
}: Props): JSX.Element => {
  const classes = useStyles();
  const theme = useTheme();
  const { t } = useTranslation();
  const [menuAnchor, setMenuAnchor] = useState<Element | null>(null);
  const [exporting, setExporting] = useState<boolean>(false);
  const { format } = useLocaleDateTimeFormat();
  const navigate = useNavigate();
  const isResourceAnomalyDetection = equals(
    resourceType,
    ResourceType.anomalydetection,
  );
  const openSizeExportMenu = (event: MouseEvent<HTMLButtonElement>): void => {
    setMenuAnchor(event.currentTarget);
  };
  const closeSizeExportMenu = (): void => {
    setMenuAnchor(null);
  };
  const getIntervalDates = useAtomValue(getDatesDerivedAtom);
  const selectedTimePeriod = useAtomValue(selectedTimePeriodAtom);

  const [start, end] = getIntervalDates(selectedTimePeriod);
  const details = useAtomValue(detailsAtom);
  const graphToCsvEndpoint = `${details?.links.endpoints.performance_graph}/download?start_date=${start}&end_date=${end}`;

  const exportToCsv = (): void => {
    window.open(graphToCsvEndpoint, 'noopener', 'noreferrer');
  };

  const goToPerformancePage = (): void => {
    const startTimestamp = format({
      date: customTimePeriod?.start as Date,
      formatString: 'X',
    });
    const endTimestamp = format({
      date: customTimePeriod?.end as Date,
      formatString: 'X',
    });

    const urlParameters = (): string => {
      const params = new URLSearchParams({
        end: endTimestamp,
        mode: '0',
        start: startTimestamp,
        svc_id: `${resourceParentName};${resourceName}`,
      });

      return params.toString();
    };

    navigate(`/main.php?p=204&${urlParameters()}`);
  };

  const convertToPng = (ratio: number): void => {
    setMenuAnchor(null);
    setExporting(true);
    exportToPng({
      backgroundColor: theme.palette.background.paper,
      element: performanceGraphRef.current as HTMLElement,
      ratio,
      title: `${resourceName}-performance`,
    }).finally(() => {
      setExporting(false);
    });
  };

  const openModalAnomalyDetection = (): void => {
    getIsModalOpened(true);
  };

  return (
    <div className={classes.buttonGroup}>
      <ContentWithCircularLoading
        alignCenter={false}
        loading={exporting}
        loadingIndicatorSize={16}
      >
        <>
          <IconButton
            disableTouchRipple
            ariaLabel={t(labelPerformancePage)}
            color="primary"
            data-testid={labelPerformancePage}
            size="small"
            title={t(labelPerformancePage)}
            onClick={goToPerformancePage}
          >
            <LaunchIcon fontSize="inherit" />
          </IconButton>
          <IconButton
            disableTouchRipple
            ariaLabel={t(labelExport)}
            data-testid={labelExport}
            disabled={isNil(timeline)}
            size="small"
            title={t(labelExport)}
            onClick={openSizeExportMenu}
          >
            <SaveAsImageIcon fontSize="inherit" />
          </IconButton>
          {isResourceAnomalyDetection && (
            <IconButton
              disableTouchRipple
              ariaLabel={t(labelPerformanceGraphAD)}
              data-testid={labelPerformanceGraphAD}
              size="small"
              title={t(labelPerformanceGraphAD)}
              onClick={openModalAnomalyDetection}
            >
              <WrenchIcon fontSize="inherit" />
            </IconButton>
          )}
          <Menu
            keepMounted
            anchorEl={menuAnchor}
            open={Boolean(menuAnchor)}
            onClose={closeSizeExportMenu}
          >
            <MenuItem data-testid={labelExport} sx={{ cursor: 'auto' }}>
              {t(labelExport)}
            </MenuItem>
            <Divider />

            <MenuItem
              data-testid={labelAsDisplayed}
              onClick={(): void => convertToPng(1)}
            >
              {t(labelAsDisplayed)}
            </MenuItem>
            <MenuItem
              data-testid={labelMediumSize}
              onClick={(): void => convertToPng(0.75)}
            >
              {t(labelMediumSize)}
            </MenuItem>
            <MenuItem
              data-testid={labelSmallSize}
              onClick={(): void => convertToPng(0.5)}
            >
              {t(labelSmallSize)}
            </MenuItem>
            <Divider />
            <MenuItem data-testid={labelCSV} onClick={exportToCsv}>
              {t(labelCSV)}
            </MenuItem>
          </Menu>
        </>
      </ContentWithCircularLoading>
    </div>
  );
};

const MemoizedGraphActions = memoizeComponent<Props>({
  Component: GraphActions,
  memoProps: [
    'customTimePeriod',
    'resourceParentName',
    'resourceName',
    'timeline',
    'performanceGraphRef',
  ],
});

export default MemoizedGraphActions;
