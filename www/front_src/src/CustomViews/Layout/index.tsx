import { FC, useEffect } from 'react';

import { Responsive } from '@visx/visx';
import GridLayout, { WidthProvider } from 'react-grid-layout';
import { useAtom, useAtomValue, useSetAtom } from 'jotai';
import { equals, find, map, propEq } from 'ramda';

import { Responsive as ResponsiveHeight } from '@centreon/ui';

import {
  breakpointAtom,
  changeLayoutDerivedAtom,
  columnsAtom,
  getBreakpoint,
  isEditingAtom,
  layoutByBreakpointDerivedAtom,
} from '../atoms';

import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

import EditionGrid from './EditionGrid';
import Widget from './Widget';

const ReactGridLayout = WidthProvider(GridLayout);

const Layout: FC = () => {
  const [layout, setLayout] = useAtom(layoutByBreakpointDerivedAtom);
  const columns = useAtomValue(columnsAtom);
  const breakpoint = useAtomValue(breakpointAtom);
  const isEditing = useAtomValue(isEditingAtom);
  const changeWidgetsLayout = useSetAtom(changeLayoutDerivedAtom);

  const changeLayout = (newLayout): void => {
    setLayout(
      map(({ i, ...other }) => {
        const widget = find(propEq('i', i), layout);

        return {
          ...other,
          i,
          widgetConfiguration: widget?.widgetConfiguration,
        };
      }, newLayout),
    );
  };

  const resize = (): void => {
    const newBreakpoint = getBreakpoint(window.innerWidth);

    if (equals(breakpoint, newBreakpoint)) {
      return;
    }

    changeWidgetsLayout({ breakpoint: newBreakpoint });
  };

  useEffect(() => {
    resize();
  }, []);

  useEffect(() => {
    window.addEventListener('resize', resize);

    return () => {
      window.removeEventListener('resize', resize);
    };
  }, [breakpoint]);

  return (
    <ResponsiveHeight>
      <Responsive.ParentSize>
        {({ width, height }): JSX.Element => (
          <>
            {isEditing && <EditionGrid height={height} width={width} />}
            <ReactGridLayout
              cols={columns}
              containerPadding={[4, 0]}
              layout={layout}
              resizeHandles={['s', 'e', 'se']}
              rowHeight={30}
              width={width}
              onLayoutChange={changeLayout}
            >
              {layout.map(({ i, widgetConfiguration }) => {
                return (
                  <div key={i}>
                    <Widget key={i} path={widgetConfiguration.path} title={i} />
                  </div>
                );
              })}
            </ReactGridLayout>
          </>
        )}
      </Responsive.ParentSize>
    </ResponsiveHeight>
  );
};

export default Layout;
