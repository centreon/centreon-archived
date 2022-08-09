import { MouseEvent, MutableRefObject, useState } from 'react';

import { isNil } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';
import { useNavigate } from 'react-router-dom';

import { Divider, Menu, MenuItem, useTheme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import SaveAsImageIcon from '@mui/icons-material/SaveAlt';
import LaunchIcon from '@mui/icons-material/Launch';

import {
  ContentWithCircularLoading,
  useLocaleDateTimeFormat,
  IconButton,
} from '@centreon/ui';

import {
  labelExport,
  labelExportToCSV,
  labelAsDisplayed,
  labelAsMediumSize,
  labelAsSmallSize,
  labelPerformancePage,
} from '../../translatedLabels';
import { CustomTimePeriod } from '../../Details/tabs/Graph/models';
import { TimelineEvent } from '../../Details/tabs/Timeline/models';
import memoizeComponent from '../../memoizedComponent';
import { detailsAtom } from '../../Details/detailsAtoms';

import exportToPng from './ExportableGraphWithTimeline/exportToPng';

interface Props {
  customTimePeriod?: CustomTimePeriod;
  performanceGraphRef: MutableRefObject<HTMLDivElement | null>;
  resourceName: string;
  resourceParentName?: string;
  timeline?: Array<TimelineEvent>;
}

const useStyles = makeStyles((theme) => ({
  buttonGroup: {
    columnGap: theme.spacing(1),
    display: 'inline',
    flexDirection: 'row',
  },
  buttonLink: {
    background: 'transparent',
    border: 'none',
  },
  labelButton: {
    fontWeight: 'bold',
  },
}));

const GraphActions = ({
  customTimePeriod,
  resourceParentName,
  resourceName,
  timeline,
  performanceGraphRef,
}: Props): JSX.Element => {
  const classes = useStyles();
  const theme = useTheme();
  const { t } = useTranslation();
  const [menuAnchor, setMenuAnchor] = useState<Element | null>(null);
  const [exporting, setExporting] = useState<boolean>(false);
  const { format } = useLocaleDateTimeFormat();
  const navigate = useNavigate();

  const openSizeExportMenu = (event: MouseEvent<HTMLButtonElement>): void => {
    setMenuAnchor(event.currentTarget);
  };
  const closeSizeExportMenu = (): void => {
    setMenuAnchor(null);
  };

  const details = useAtomValue(detailsAtom);
  const downloadGraphFile = `${details?.links.endpoints.metrics}/download?start_date=2022-08-01T00:00:22Z&end_date=2022-08-08T18:00:22Z`;

  const exportToCsv = (): void => {
    window.open(downloadGraphFile, 'noopener', 'noreferrer');
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
      backgroundColor: theme.palette.background.default,
      element: performanceGraphRef.current as HTMLElement,
      ratio,
      title: `${resourceName}-performance`,
    }).finally(() => {
      setExporting(false);
    });
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
            className={classes.buttonLink}
            color="primary"
            data-testid={labelPerformancePage}
            size="small"
            title={t(labelPerformancePage)}
            onClick={goToPerformancePage}
          >
            <LaunchIcon style={{ fontSize: 18 }} />
          </IconButton>
          <IconButton
            disableTouchRipple
            ariaLabel={t(labelExport)}
            data-testid={labelExport}
            disabled={isNil(timeline)}
            size="large"
            title={t(labelExport)}
            onClick={openSizeExportMenu}
          >
            <SaveAsImageIcon style={{ fontSize: 18 }} />
          </IconButton>
          <Menu
            keepMounted
            anchorEl={menuAnchor}
            open={Boolean(menuAnchor)}
            onClose={closeSizeExportMenu}
          >
            <MenuItem
              className={classes.labelButton}
              data-testid={labelExport}
              sx={{ cursor: 'auto' }}
            >
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
              data-testid={labelAsMediumSize}
              onClick={(): void => convertToPng(0.75)}
            >
              {t(labelAsMediumSize)}
            </MenuItem>
            <MenuItem
              data-testid={labelAsSmallSize}
              onClick={(): void => convertToPng(0.5)}
            >
              {t(labelAsSmallSize)}
            </MenuItem>
            <Divider />
            <MenuItem
              className={classes.labelButton}
              data-testid={labelExportToCSV}
              onClick={exportToCsv}
            >
              {t(labelExportToCSV)}
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
